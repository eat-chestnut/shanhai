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
var _log_label: RichTextLabel
var _player
var _enemies: Array = []
var _defeated_monsters: Array = []
var _battle_active := false
var _battle_over := false
var _elapsed := 0.0

func _ready() -> void:
	_build_ui()
	set_process(false)

func activate() -> void:
	call_deferred("_start_battle")

func deactivate() -> void:
	_clear_battle()
	set_process(false)

func _process(delta: float) -> void:
	if not _battle_active or _battle_over:
		return
	_elapsed += delta
	_timer_label.text = "耗时 %.1f 秒" % _elapsed
	if _player != null and not _player.is_alive():
		_finish_battle(false)
		return
	var alive_enemy_count := 0
	for enemy in _enemies:
		if enemy != null and enemy.is_alive():
			alive_enemy_count += 1
	if alive_enemy_count == 0 and _player != null:
		_finish_battle(true)

func _start_battle() -> void:
	if BattleState.current_context.is_empty():
		return
	await get_tree().process_frame
	_clear_battle()
	_battle_active = true
	_battle_over = false
	_elapsed = 0.0
	set_process(true)

	var context := BattleState.current_context
	_context_label.text = _context_text(context)
	_append_log("巡厄开始。方向键控制角色移动，进入攻击范围后将自动出手。")

	var arena_bounds := Rect2(Vector2(60, 80), _arena_host.size - Vector2(120, 120))
	var player_stats := PlayerState.get_total_stats()
	_player = PLAYER_SCENE.instantiate()
	_player.global_position = Vector2(arena_bounds.position.x + 90.0, arena_bounds.get_center().y)
	_arena_root.add_child(_player)
	_player.setup_actor({
		"display_name": GameData.get_character_class_name(str(PlayerState.player.get("class_id", ""))),
		"body_color": Color("d7a04f"),
		"max_hp": float(player_stats.get("max_hp", 850)),
		"attack": float(player_stats.get("atk", 30)),
		"defense": float(player_stats.get("def", 15)),
		"move_speed": 190.0,
		"attack_range": 84.0,
		"attack_interval": 1.0,
		"is_player": true,
		"boss_damage_bonus": float(player_stats.get("boss_dmg", 0)),
		"arena_bounds": arena_bounds
	})
	_player.attacked.connect(_on_actor_attacked)
	_player.combat_event.connect(_append_log)
	_player.died.connect(_on_actor_died)

	var enemy_ids := BattleState.build_monster_ids()
	for index in enemy_ids.size():
		var monster_data := GameData.get_monster(str(enemy_ids[index]))
		if monster_data.is_empty():
			continue
		var enemy = ENEMY_SCENE.instantiate()
		var difficulty_multiplier := _difficulty_multiplier(str(context.get("difficulty_id", "easy")))
		enemy.global_position = Vector2(arena_bounds.end.x - 90.0 - float(index * 58), arena_bounds.position.y + 130.0 + float(index % 3) * 180.0)
		_arena_root.add_child(enemy)
		enemy.setup_actor({
			"display_name": str(monster_data.get("name", "怪物")),
			"body_color": ShanhaiStyle.BOSS if bool(monster_data.get("is_boss", false)) else Color("9f5449"),
			"max_hp": float(monster_data.get("base_hp", 400)) * difficulty_multiplier,
			"attack": float(monster_data.get("base_atk", 35)) * (0.88 + difficulty_multiplier * 0.12),
			"defense": 10.0 * difficulty_multiplier,
			"move_speed": 130.0 if bool(monster_data.get("is_boss", false)) else 118.0,
			"attack_range": 78.0 if bool(monster_data.get("is_boss", false)) else 68.0,
			"attack_interval": 1.45 if bool(monster_data.get("is_boss", false)) else 1.7,
			"aggro_range": 230.0 if bool(monster_data.get("is_boss", false)) else 190.0,
			"is_boss": bool(monster_data.get("is_boss", false)),
			"arena_bounds": arena_bounds
		})
		enemy.player_actor = _player
		enemy.attacked.connect(_on_actor_attacked)
		enemy.combat_event.connect(_append_log)
		enemy.died.connect(_on_actor_died)
		enemy.set_meta("monster_id", str(monster_data.get("monster_id", "")))
		_enemies.append(enemy)

	_player.enemies = _enemies
	_power_label.text = "当前战力 %d / 建议 %d" % [PlayerState.get_power(), int(context.get("recommended_power", 0))]

func _finish_battle(victory: bool) -> void:
	if _battle_over:
		return
	_battle_over = true
	_battle_active = false
	set_process(false)
	BattleState.finish_battle(victory, _defeated_monsters, _elapsed)
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
	_elapsed = 0.0
	_defeated_monsters.clear()
	_enemies.clear()
	_player = null
	if _log_label != null:
		_log_label.clear()
	if _arena_root != null:
		for child in _arena_root.get_children():
			child.queue_free()

func _difficulty_multiplier(difficulty_id: String) -> float:
	match difficulty_id:
		"easy":
			return 1.0
		"normal":
			return 1.35
		"hard":
			return 1.75
		_:
			return 1.0

func _context_text(context: Dictionary) -> String:
	if str(context.get("mode", "")) == "mainline":
		return "%s / %s / %s" % [context.get("chapter_name", "主线"), context.get("node_name", "节点"), context.get("difficulty_id", "")]
	return "%s / %s" % [context.get("dungeon_name", "副本"), context.get("difficulty_id", "")]

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
	hint.text = "战斗机制：自动普攻、Boss追击、灼烧/回春/控制状态会在战斗中持续结算。"
	hint.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(hint, true, 16)
	footer_box.add_child(hint)

	_log_label = RichTextLabel.new()
	_log_label.size_flags_vertical = Control.SIZE_EXPAND_FILL
	_log_label.bbcode_enabled = false
	_log_label.scroll_active = true
	footer_box.add_child(_log_label)
