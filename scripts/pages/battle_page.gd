extends Control
class_name BattlePage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal battle_finished

const PLAYER_SCENE := preload("res://scenes/battle/entities/player_actor.tscn")
const ENEMY_SCENE := preload("res://scenes/battle/entities/enemy_actor.tscn")

var _arena_host: Control
var _arena_root: Node2D
var _context_label: Label
var _power_label: Label
var _timer_label: Label
var _resource_label: Label
var _skill_label: Label
var _telegraph_label: Label
var _log_label: RichTextLabel
var _player
var _enemies: Array = []
var _defeated_monsters: Array = []
var _battle_active := false
var _battle_over := false
var _battle_finishing := false
var _elapsed := 0.0

func _ready() -> void:
	_build_ui()
	set_process(false)

func activate() -> void:
	call_deferred("_prepare_and_start_battle")

func deactivate() -> void:
	_clear_battle()
	set_process(false)

func _process(delta: float) -> void:
	if not _battle_active or _battle_over:
		return
	_elapsed += delta
	_timer_label.text = "耗时 %.1f 秒" % _elapsed
	if _player != null and not _player.is_alive():
		_queue_finish(false)
		return
	var alive_enemy_count := 0
	for enemy in _enemies:
		if enemy != null and enemy.is_alive():
			alive_enemy_count += 1
	if alive_enemy_count == 0 and _player != null:
		_queue_finish(true)

func _prepare_and_start_battle() -> void:
	if BattleState.current_context.is_empty():
		return
	await get_tree().process_frame
	_clear_battle()
	_context_label.text = "正在向后端申请巡厄战场..."
	_append_log("巡厄准备中，优先校验正式运行态 battle prepare。")

	var battle_payload := await BattleState.prepare_current_battle()
	if battle_payload.is_empty():
		_append_log("战斗准备失败：%s" % (GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else "请稍后重试"))
		return

	_battle_active = true
	_battle_over = false
	_battle_finishing = false
	_elapsed = 0.0
	set_process(true)

	var context := BattleState.current_context
	var player_snapshot: Dictionary = battle_payload.get("player_snapshot", {})
	var enemy_group_snapshot: Dictionary = battle_payload.get("enemy_group_snapshot", {})
	_context_label.text = _context_text(context)
	_append_log("巡厄开始。方向键控制角色移动，进入攻击范围后将自动出手。")

	var arena_bounds := Rect2(Vector2(60, 80), _arena_host.size - Vector2(120, 120))
	var player_stats: Dictionary = player_snapshot.get("stats", PlayerState.get_total_stats())
	var player_skills: Dictionary = player_snapshot.get("skills", {})
	var class_profile: Dictionary = player_snapshot.get("class_profile", PlayerState.get_class_profile())
	_player = PLAYER_SCENE.instantiate()
	_player.global_position = Vector2(arena_bounds.position.x + 90.0, arena_bounds.get_center().y)
	_arena_root.add_child(_player)
	_player.setup_actor({
		"display_name": GameData.get_character_class_name(str(player_snapshot.get("class_id", PlayerState.player.get("class_id", "")))),
		"body_color": Color("d7a04f"),
		"max_hp": float(player_stats.get("max_hp", 850)),
		"attack": float(player_stats.get("atk", 30)),
		"defense": float(player_stats.get("def", 15)),
		"move_speed": float(class_profile.get("move_speed", 190.0)),
		"attack_range": float(class_profile.get("attack_range", 84.0)),
		"attack_interval": float(class_profile.get("attack_interval", 1.0)),
		"is_player": true,
		"boss_damage_bonus": float(player_stats.get("boss_dmg", 0)),
		"attack_speed_bonus": float(player_stats.get("attack_speed_bonus", 0.0)),
		"arena_bounds": arena_bounds,
		"class_id": str(player_snapshot.get("class_id", PlayerState.player.get("class_id", ""))),
		"resource_name": str(player_snapshot.get("resource_name", PlayerState.get_resource_name())),
		"resource_max": float(player_snapshot.get("max_energy", PlayerState.get_max_energy())),
		"class_profile": class_profile,
		"active_skills": player_skills.get("active", PlayerState.get_runtime_skills("active")),
		"passive_skills": player_skills.get("passive", PlayerState.get_runtime_skills("passive"))
	})
	_player.attacked.connect(_on_actor_attacked)
	_player.combat_event.connect(_append_log)
	_player.died.connect(_on_actor_died)
	_player.skill_state_changed.connect(_on_skill_state_changed)

	var monsters: Array = enemy_group_snapshot.get("monsters", [])
	for index in monsters.size():
		var enemy_snapshot: Dictionary = monsters[index]
		_spawn_enemy_from_snapshot(enemy_snapshot, arena_bounds, index)

	_player.enemies = _enemies
	_power_label.text = "当前战力 %d / 建议 %d" % [PlayerState.get_power(), int(context.get("recommended_power", 0))]
	_apply_telegraph("当前职业：%s  战斗定位：%s" % [
		GameData.get_character_class_name(str(player_snapshot.get("class_id", ""))),
		str(class_profile.get("role", "adventurer"))
	])
	_on_skill_state_changed([], float(PlayerState.get_max_energy()), float(PlayerState.get_max_energy()), PlayerState.get_resource_name())

func _queue_finish(victory: bool) -> void:
	if _battle_finishing:
		return
	_battle_finishing = true
	_battle_over = true
	_battle_active = false
	set_process(false)
	call_deferred("_finish_battle_async", victory)

func _finish_battle_async(victory: bool) -> void:
	var result := await BattleState.finish_battle(victory, _defeated_monsters, _elapsed)
	if result.is_empty():
		_append_log("结算失败：%s" % (GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else "请稍后重试"))
		_battle_active = false
		_battle_over = false
		_battle_finishing = false
		return
	emit_signal("battle_finished")

func _on_actor_attacked(attacker, target, damage: int) -> void:
	if damage <= 0:
		return
	_append_log("%s 对 %s 造成 %d 伤害" % [attacker.display_name, target.display_name, damage])

func _on_actor_died(actor) -> void:
	_append_log("%s 倒下了。" % actor.display_name)
	if actor == _player:
		return
	_defeated_monsters.append({
		"monster_id": str(actor.get_meta("monster_id", "")),
		"name": actor.display_name,
		"is_boss": actor.is_boss
	})

func _append_log(message: String) -> void:
	if _log_label == null:
		return
	_log_label.append_text("%s\n" % message)
	_log_label.scroll_to_line(_log_label.get_line_count())

func _clear_battle() -> void:
	_battle_active = false
	_battle_over = false
	_battle_finishing = false
	_elapsed = 0.0
	_defeated_monsters.clear()
	_enemies.clear()
	_player = null
	if _log_label != null:
		_log_label.clear()
	if _resource_label != null:
		_resource_label.text = ""
	if _skill_label != null:
		_skill_label.text = ""
	if _telegraph_label != null:
		_telegraph_label.text = ""
	if _arena_root != null:
		for child in _arena_root.get_children():
			child.queue_free()

func _context_text(context: Dictionary) -> String:
	if str(context.get("mode", "")) == "mainline":
		return "%s / %s / %s" % [context.get("chapter_name", "主线"), context.get("node_name", "节点"), context.get("difficulty_id", "")]
	if str(context.get("mode", "")) == "scripture":
		return "%s / 世界等级 %d" % [context.get("scripture_name", "经卷"), int(context.get("world_level", 0))]
	if str(context.get("mode", "")) == "challenge":
		return "%s / %s" % [context.get("challenge_name", "挑战"), context.get("floor_name", context.get("difficulty_id", ""))]
	return "%s / %s" % [context.get("dungeon_name", "副本"), context.get("difficulty_id", "")]

func _on_skill_state_changed(skill_states: Array, current_resource: float, max_resource: float, resource_name: String) -> void:
	if _resource_label == null or _skill_label == null:
		return
	_resource_label.text = "%s %.0f / %.0f" % [resource_name, current_resource, max_resource]
	if skill_states.is_empty():
		_skill_label.text = "主动技能：战斗加载中"
		return
	var parts: Array = []
	for state in skill_states:
		var cd := float(state.get("cooldown_left", 0.0))
		var suffix := "冷却 %.1fs" % cd if cd > 0.0 else "就绪"
		parts.append("%s Lv.%d [%s]" % [
			state.get("skill_name", state.get("skill_id", "技能")),
			int(state.get("level", 1)),
			suffix
		])
	_skill_label.text = "主动技能：%s" % " / ".join(parts)

func _spawn_enemy_from_snapshot(enemy_snapshot: Dictionary, arena_bounds: Rect2, index: int) -> void:
	var enemy_stats: Dictionary = enemy_snapshot.get("stats", {})
	var enemy = ENEMY_SCENE.instantiate()
	enemy.global_position = Vector2(arena_bounds.end.x - 90.0 - float(index * 58), arena_bounds.position.y + 130.0 + float(index % 3) * 180.0)
	_arena_root.add_child(enemy)
	enemy.setup_actor({
		"display_name": str(enemy_snapshot.get("name", "怪物")),
		"body_color": ShanhaiStyle.BOSS if bool(enemy_snapshot.get("is_boss", false)) else Color("9f5449"),
		"max_hp": float(enemy_stats.get("max_hp", 400)),
		"attack": float(enemy_stats.get("attack", 35)),
		"defense": float(enemy_stats.get("defense", 10)),
		"move_speed": float(enemy_stats.get("move_speed", 118.0)),
		"attack_range": float(enemy_stats.get("attack_range", 68.0)),
		"attack_interval": float(enemy_stats.get("attack_interval", 1.7)),
		"aggro_range": float(enemy_stats.get("aggro_range", 190.0)),
		"is_boss": bool(enemy_snapshot.get("is_boss", false)),
		"arena_bounds": arena_bounds,
		"skill_profile": enemy_snapshot.get("skill_profile", {}).duplicate(true)
	})
	enemy.player_actor = _player
	enemy.attacked.connect(_on_actor_attacked)
	enemy.combat_event.connect(_append_log)
	enemy.died.connect(_on_actor_died)
	enemy.telegraph_requested.connect(_apply_telegraph)
	enemy.spawn_requested.connect(_on_enemy_spawn_requested.bind(arena_bounds))
	enemy.set_meta("monster_id", str(enemy_snapshot.get("monster_id", "")))
	_enemies.append(enemy)

func _on_enemy_spawn_requested(monster_id: String, count: int, source_actor, arena_bounds: Rect2) -> void:
	if monster_id.is_empty() or source_actor == null:
		return
	var spawn_count: int = min(max(count, 1), 2)
	_apply_telegraph("%s 正在召来新的敌人。", [str(source_actor.display_name)])
	for spawn_index in range(spawn_count):
		var snapshot := _build_spawn_snapshot(monster_id)
		snapshot["name"] = "%s·召影" % snapshot.get("name", monster_id)
		snapshot["is_boss"] = false
		_spawn_enemy_from_snapshot(snapshot, arena_bounds, _enemies.size() + spawn_index)
	_player.enemies = _enemies

func _build_spawn_snapshot(monster_id: String) -> Dictionary:
	var is_scripture := str(BattleState.current_context.get("mode", "")) == "scripture"
	var monster := GameData.get_scripture_monster(monster_id) if is_scripture else GameData.get_monster(monster_id)
	var difficulty_multiplier := _difficulty_multiplier(str(BattleState.current_battle_payload.get("difficulty_id", BattleState.current_context.get("difficulty_id", "easy"))))
	var behavior_profile: Dictionary = monster.get("behavior_profile", {})
	var tier_rules: Dictionary = BattleState.current_context.get("battle_rules_snapshot", {})
	var hp_scale := float(tier_rules.get("hp_scale", 1.0)) if is_scripture else difficulty_multiplier
	var atk_scale := float(tier_rules.get("atk_scale", 1.0)) if is_scripture else (0.88 + difficulty_multiplier * 0.12)
	var def_scale := float(tier_rules.get("def_scale", 1.0)) if is_scripture else difficulty_multiplier
	return {
		"monster_id": monster_id,
		"name": str(monster.get("name", monster_id)),
		"is_boss": bool(monster.get("is_boss", false)),
		"skill_profile": behavior_profile.duplicate(true),
		"stats": {
			"max_hp": int(float(monster.get("base_hp", 400)) * hp_scale * float(behavior_profile.get("hp_ratio", 1.0))),
			"attack": int(float(monster.get("base_atk", 35)) * atk_scale * float(behavior_profile.get("attack_ratio", 1.0))),
			"defense": int(float(monster.get("base_def", behavior_profile.get("base_defense", 10))) * def_scale),
			"move_speed": float(monster.get("move_speed", behavior_profile.get("move_speed", 118.0))),
			"attack_range": float(behavior_profile.get("attack_range", 78.0 if bool(monster.get("is_boss", false)) else 68.0)),
			"attack_interval": float(behavior_profile.get("attack_interval", 1.45 if bool(monster.get("is_boss", false)) else 1.7)),
			"aggro_range": float(behavior_profile.get("aggro_range", 230.0 if bool(monster.get("is_boss", false)) else 190.0))
		}
	}

func _difficulty_multiplier(difficulty_id: String) -> float:
	match difficulty_id:
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

func _apply_telegraph(message: String, args: Array = []) -> void:
	var final_message := message % args if not args.is_empty() else message
	if _telegraph_label != null:
		_telegraph_label.text = "战场预警：%s" % final_message
	_append_log(final_message)

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	anchor_right = 1.0
	anchor_bottom = 1.0

	var root := VBoxContainer.new()
	root.anchor_right = 1.0
	root.anchor_bottom = 1.0
	root.size_flags_vertical = Control.SIZE_EXPAND_FILL
	root.add_theme_constant_override("separation", 14)
	add_child(root)

	var header := PanelContainer.new()
	ShanhaiStyle.apply_panel(header, true)
	root.add_child(header)

	var header_box := VBoxContainer.new()
	header_box.add_theme_constant_override("separation", 6)
	header.add_child(header_box)

	_context_label = Label.new()
	ShanhaiStyle.apply_title(_context_label, 28)
	header_box.add_child(_context_label)

	_power_label = Label.new()
	ShanhaiStyle.apply_body(_power_label, false, 18)
	header_box.add_child(_power_label)

	_timer_label = Label.new()
	ShanhaiStyle.apply_body(_timer_label, true, 16)
	header_box.add_child(_timer_label)

	_arena_host = Control.new()
	_arena_host.size_flags_vertical = Control.SIZE_EXPAND_FILL
	_arena_host.custom_minimum_size = Vector2(0, 620)
	root.add_child(_arena_host)

	var arena_bg := ColorRect.new()
	arena_bg.anchor_right = 1.0
	arena_bg.anchor_bottom = 1.0
	arena_bg.color = Color("221b16")
	_arena_host.add_child(arena_bg)

	_arena_root = Node2D.new()
	_arena_host.add_child(_arena_root)

	var footer := PanelContainer.new()
	ShanhaiStyle.apply_panel(footer)
	footer.custom_minimum_size = Vector2(0, 250)
	root.add_child(footer)

	var footer_box := VBoxContainer.new()
	footer_box.add_theme_constant_override("separation", 10)
	footer.add_child(footer_box)

	var hint := Label.new()
	hint.text = "战斗机制：实时表现仍由客户端负责，正式运行态会优先走后端 prepare / settle 校验奖励与进度。"
	hint.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(hint, true, 16)
	footer_box.add_child(hint)

	_resource_label = Label.new()
	ShanhaiStyle.apply_heading(_resource_label, 18)
	footer_box.add_child(_resource_label)

	_telegraph_label = Label.new()
	_telegraph_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_telegraph_label, true, 16)
	footer_box.add_child(_telegraph_label)

	_skill_label = Label.new()
	_skill_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_skill_label, false, 16)
	footer_box.add_child(_skill_label)

	_log_label = RichTextLabel.new()
	_log_label.size_flags_vertical = Control.SIZE_EXPAND_FILL
	_log_label.bbcode_enabled = false
	_log_label.scroll_active = true
	footer_box.add_child(_log_label)
