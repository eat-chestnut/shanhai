extends Node

signal context_changed(context: Dictionary)
signal result_ready(result: Dictionary)

var current_context: Dictionary = {}
var first_clear_records: Dictionary = {}
var last_result: Dictionary = {}
var current_battle_payload: Dictionary = {}
var _rng := RandomNumberGenerator.new()

func _ready() -> void:
	_rng.randomize()

func start_mainline(chapter: Dictionary, node: Dictionary, difficulty: Dictionary) -> void:
	set_context({
		"mode": "mainline",
		"chapter_id": chapter.get("chapter_id", ""),
		"chapter_name": chapter.get("chapter_name", ""),
		"node_id": node.get("node_id", ""),
		"node_name": node.get("node_name", ""),
		"difficulty_id": difficulty.get("difficulty_id", ""),
		"recommended_power": int(difficulty.get("recommended_power", 0)),
		"first_clear_reward_group_id": str(difficulty.get("first_clear_reward_group_id", ""))
	})

func start_dungeon(dungeon: Dictionary, difficulty: Dictionary) -> void:
	set_context({
		"mode": "dungeon",
		"dungeon_id": dungeon.get("dungeon_id", ""),
		"dungeon_name": dungeon.get("dungeon_name", ""),
		"difficulty_id": difficulty.get("difficulty_id", ""),
		"recommended_power": int(difficulty.get("recommended_power", 0)),
		"first_clear_reward_group_id": str(
			difficulty.get(
				"first_clear_reward_group_id",
				"reward_%s_%s" % [dungeon.get("dungeon_id", ""), difficulty.get("difficulty_id", "")]
			)
		)
	})

func set_context(context: Dictionary) -> void:
	current_context = context.duplicate(true)
	current_battle_payload = {}
	last_result = {}
	emit_signal("context_changed", current_context)

func prepare_current_battle() -> Dictionary:
	if current_context.is_empty():
		return {}

	var source_type := "stage" if str(current_context.get("mode", "")) == "mainline" else "dungeon"
	var source_id := str(current_context.get("node_id", current_context.get("dungeon_id", "")))
	var difficulty_id := str(current_context.get("difficulty_id", ""))

	if GameData.using_runtime_backend:
		var payload := await GameApi.battle_prepare(source_type, source_id, difficulty_id)
		if not payload.is_empty():
			current_battle_payload = payload.duplicate(true)
			return current_battle_payload
		if GameApi.is_business_error():
			GameData.last_runtime_error = GameApi.get_last_error_message()
			return {}

	# Fallback only: prepare 接口失败时继续保留本地可玩战斗闭环。
	if GameApi.can_use_transport_fallback() or not GameData.using_runtime_backend:
		current_battle_payload = _build_local_prepare_payload(source_type, source_id, difficulty_id)
		return current_battle_payload

	return {}

func finish_battle(victory: bool, defeated_monsters: Array, elapsed_seconds: float) -> Dictionary:
	if GameData.using_runtime_backend and not current_battle_payload.is_empty() and not str(current_battle_payload.get("battle_id", "")).begins_with("local_"):
		var settle_payload := {
			"battle_id": str(current_battle_payload.get("battle_id", "")),
			"result": "victory" if victory else "defeat",
			"duration": elapsed_seconds,
			"cleared_wave": 1 if victory else 0,
			"client_summary": {
				"defeated_monsters": defeated_monsters.duplicate(true)
			}
		}
		var server_result := await GameApi.battle_settle(settle_payload)
		if not server_result.is_empty():
			await GameData.refresh_runtime_state(false)
			GameData.last_runtime_error = ""
			last_result = _build_runtime_result(server_result, victory, defeated_monsters, elapsed_seconds)
			emit_signal("result_ready", last_result)
			return last_result
		if GameApi.is_business_error():
			GameData.last_runtime_error = GameApi.get_last_error_message()
			last_result = {}
			return {}

	# Fallback only: 正式运行态结算失败时才允许本地发奖励、写首通和入包。
	if GameApi.can_use_transport_fallback() or not GameData.using_runtime_backend:
		return _finish_battle_fallback(victory, defeated_monsters, elapsed_seconds)

	return {}

func get_context_key() -> String:
	if current_context.is_empty():
		return ""
	if str(current_context.get("mode", "")) == "mainline":
		return "%s::%s::%s" % [current_context.get("chapter_id", ""), current_context.get("node_id", ""), current_context.get("difficulty_id", "")]
	return "%s::%s" % [current_context.get("dungeon_id", ""), current_context.get("difficulty_id", "")]

func build_monster_ids() -> Array:
	var monsters: Array = current_battle_payload.get("enemy_group_snapshot", {}).get("monsters", [])
	if not monsters.is_empty():
		return monsters.map(func(entry: Dictionary) -> String: return str(entry.get("monster_id", "")))
	var difficulty_id := str(current_context.get("difficulty_id", ""))
	if str(current_context.get("mode", "")) == "mainline":
		return GameData.get_mainline_encounter(str(current_context.get("node_id", "")), difficulty_id)
	return GameData.get_dungeon_encounter(str(current_context.get("dungeon_id", "")), difficulty_id)

func get_player_snapshot() -> Dictionary:
	return current_battle_payload.get("player_snapshot", {}).duplicate(true)

func get_enemy_group_snapshot() -> Dictionary:
	return current_battle_payload.get("enemy_group_snapshot", {}).duplicate(true)

func _build_local_prepare_payload(source_type: String, source_id: String, difficulty_id: String) -> Dictionary:
	var battle_id := "local_%s" % Time.get_unix_time_from_system()
	var battle_seed := randi()
	var player_stats := PlayerState.get_total_stats()
	var active_skills := PlayerState.get_runtime_skills("active")
	var passive_skills := PlayerState.get_runtime_skills("passive")
	var class_profile := PlayerState.get_class_profile()
	var monsters: Array = []

	for monster_id in build_monster_ids():
		var monster_data := GameData.get_monster(str(monster_id))
		if monster_data.is_empty():
			continue
		var difficulty_multiplier := _difficulty_multiplier(difficulty_id)
		monsters.append({
			"monster_id": str(monster_data.get("monster_id", "")),
			"name": str(monster_data.get("name", "怪物")),
			"is_boss": bool(monster_data.get("is_boss", false)),
			"stats": {
				"max_hp": int(float(monster_data.get("base_hp", 400)) * difficulty_multiplier),
				"attack": int(float(monster_data.get("base_atk", 35)) * (0.88 + difficulty_multiplier * 0.12)),
				"defense": int(10.0 * difficulty_multiplier),
				"move_speed": 130.0 if bool(monster_data.get("is_boss", false)) else 118.0,
				"attack_range": 78.0 if bool(monster_data.get("is_boss", false)) else 68.0,
				"attack_interval": 1.45 if bool(monster_data.get("is_boss", false)) else 1.7,
				"aggro_range": 230.0 if bool(monster_data.get("is_boss", false)) else 190.0
			},
			"skill_profile": _boss_skill_profile(monster_data)
		})

	return {
		"battle_id": battle_id,
		"source_type": source_type,
		"source_id": source_id,
		"difficulty_id": difficulty_id,
		"battle_map_id": "local_map_%s_%s" % [source_type, source_id],
		"battle_seed": battle_seed,
		"player_snapshot": {
			"player_id": int(PlayerState.player.get("player_id", 10001)),
			"class_id": str(PlayerState.player.get("class_id", "")),
			"level": PlayerState.get_level(),
			"resource_name": PlayerState.get_resource_name(),
			"max_energy": PlayerState.get_max_energy(),
			"class_profile": class_profile,
			"stats": player_stats,
			"skills": {
				"active": active_skills,
				"passive": passive_skills
			}
		},
		"enemy_group_snapshot": {
			"monster_group_id": "%s_%s_%s" % [source_type, source_id, difficulty_id],
			"monsters": monsters
		}
	}

func _finish_battle_fallback(victory: bool, defeated_monsters: Array, elapsed_seconds: float) -> Dictionary:
	var rewards: Array = []
	var context_key := get_context_key()
	var reward_group_id := str(current_context.get("first_clear_reward_group_id", ""))
	var first_clear: bool = victory and not bool(first_clear_records.get(context_key, false))

	if first_clear and not reward_group_id.is_empty():
		rewards.append_array(GameData.get_reward_group_items(reward_group_id))
		first_clear_records[context_key] = true

	if victory:
		for monster in defeated_monsters:
			rewards.append_array(_roll_monster_drops(str(monster.get("monster_id", ""))))

	rewards = _decorate_rewards(_merge_rewards(rewards))
	if victory:
		PlayerState.apply_rewards(rewards)
	else:
		PlayerState.restore_hp()

	last_result = {
		"victory": victory,
		"first_clear": first_clear,
		"context": current_context.duplicate(true),
		"rewards": rewards,
		"first_clear_rewards": _decorate_rewards(GameData.get_reward_group_items(reward_group_id) if first_clear else []),
		"all_rewards": rewards,
		"elapsed_seconds": elapsed_seconds,
		"defeated_monsters": defeated_monsters.duplicate(true),
		"used_fallback": true
	}
	emit_signal("result_ready", last_result)
	return last_result

func _build_runtime_result(server_result: Dictionary, victory: bool, defeated_monsters: Array, elapsed_seconds: float) -> Dictionary:
	var all_rewards: Array = server_result.get("all_rewards", [])
	return {
		"victory": victory,
		"first_clear": not server_result.get("first_clear_rewards", []).is_empty(),
		"context": current_context.duplicate(true),
		"rewards": _decorate_rewards(server_result.get("rewards", [])),
		"first_clear_rewards": _decorate_rewards(server_result.get("first_clear_rewards", [])),
		"all_rewards": _decorate_rewards(all_rewards),
		"elapsed_seconds": elapsed_seconds,
		"defeated_monsters": defeated_monsters.duplicate(true),
		"progress_update": server_result.get("progress_update", {}).duplicate(true),
		"inventory_update": server_result.get("inventory_update", {}).duplicate(true),
		"currencies_update": server_result.get("currencies_update", {}).duplicate(true),
		"used_fallback": false
	}

func _roll_monster_drops(monster_id: String) -> Array:
	var rewards: Array = []
	for drop in GameData.monster_drops:
		if str(drop.get("monster_id", "")) != monster_id:
			continue
		var drop_rate := float(drop.get("drop_rate", 0.0))
		var drop_kind := str(drop.get("drop_kind", "normal"))
		var guaranteed := drop_kind == "boss_fixed" or drop_rate >= 0.999
		if guaranteed or _rng.randf() <= drop_rate:
			rewards.append({
				"item_id": str(drop.get("item_id", "")),
				"count": 1
			})
	return rewards

func _merge_rewards(rewards: Array) -> Array:
	var counts := {}
	for reward in rewards:
		var item_id := str(reward.get("item_id", ""))
		if item_id.is_empty():
			continue
		counts[item_id] = int(counts.get(item_id, 0)) + int(reward.get("count", 0))

	var merged: Array = []
	for item_id in counts.keys():
		merged.append({
			"item_id": item_id,
			"count": int(counts[item_id])
		})
	merged.sort_custom(func(a: Dictionary, b: Dictionary) -> bool:
		return str(a.get("item_id", "")) < str(b.get("item_id", ""))
	)
	return merged

func _decorate_rewards(rewards: Array) -> Array:
	var decorated: Array = []
	for reward in rewards:
		var entry: Dictionary = reward.duplicate(true)
		entry["definition"] = GameData.get_item_definition(str(entry.get("item_id", "")))
		decorated.append(entry)
	return decorated

func _difficulty_multiplier(difficulty_id: String) -> float:
	match difficulty_id:
		"easy":
			return 1.0
		"normal":
			return 1.35
		"hard":
			return 1.75
		"nightmare":
			return 2.15
		"epic":
			return 2.55
		_:
			return 1.0

func _boss_skill_profile(monster_data: Dictionary) -> Dictionary:
	var behavior_profile: Dictionary = monster_data.get("behavior_profile", {})
	if not behavior_profile.is_empty():
		return behavior_profile.duplicate(true)
	var monster_id := str(monster_data.get("monster_id", ""))
	if monster_id == "mon_new_boss":
		return {
			"name": "雷狱震落",
			"cooldown": 5.5,
			"burst_ratio": 0.4,
			"control_name": "雷缚",
			"control_duration": 1.6,
			"dot_name": "感电",
			"dot_ratio": 0.24,
			"dot_duration": 4.0,
			"self_hot_name": "雷兽回潮",
			"self_hot_ratio": 0.08
		}
	if bool(monster_data.get("is_boss", false)):
		return {
			"name": "狐火震慑",
			"cooldown": 6.0,
			"burst_ratio": 0.22,
			"control_name": "震慑",
			"control_duration": 1.2,
			"dot_name": "妖火",
			"dot_ratio": 0.2,
			"dot_duration": 4.0
		}
	return {}
