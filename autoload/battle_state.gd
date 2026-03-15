extends Node

signal context_changed(context: Dictionary)
signal result_ready(result: Dictionary)

var current_context: Dictionary = {}
var first_clear_records: Dictionary = {}
var last_result: Dictionary = {}
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
		"first_clear_reward_group_id": "reward_%s_%s" % [dungeon.get("dungeon_id", ""), difficulty.get("difficulty_id", "")]
	})

func set_context(context: Dictionary) -> void:
	current_context = context.duplicate(true)
	last_result = {}
	emit_signal("context_changed", current_context)

func finish_battle(victory: bool, defeated_monsters: Array, elapsed_seconds: float) -> Dictionary:
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

	rewards = _merge_rewards(rewards)
	if victory:
		PlayerState.apply_rewards(rewards)
	else:
		PlayerState.restore_hp()

	last_result = {
		"victory": victory,
		"first_clear": first_clear,
		"context": current_context.duplicate(true),
		"rewards": rewards,
		"elapsed_seconds": elapsed_seconds,
		"defeated_monsters": defeated_monsters.duplicate(true)
	}
	emit_signal("result_ready", last_result)
	return last_result

func get_context_key() -> String:
	if current_context.is_empty():
		return ""
	if str(current_context.get("mode", "")) == "mainline":
		return "%s::%s::%s" % [current_context.get("chapter_id", ""), current_context.get("node_id", ""), current_context.get("difficulty_id", "")]
	return "%s::%s" % [current_context.get("dungeon_id", ""), current_context.get("difficulty_id", "")]

func build_monster_ids() -> Array:
	if str(current_context.get("mode", "")) == "mainline":
		return GameData.get_mainline_encounter(str(current_context.get("node_id", "")))
	return GameData.get_dungeon_encounter(str(current_context.get("dungeon_id", "")))

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
			"count": int(counts[item_id]),
			"definition": GameData.get_item_definition(str(item_id))
		})
	merged.sort_custom(func(a: Dictionary, b: Dictionary) -> bool:
		return str(a.get("item_id", "")) < str(b.get("item_id", ""))
	)
	return merged
