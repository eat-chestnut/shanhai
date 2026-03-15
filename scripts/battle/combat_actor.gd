extends CharacterBody2D

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal died(actor)
signal attacked(attacker, target, damage)
signal combat_event(message: String)

var display_name: String = "目标"
var body_color: Color = ShanhaiStyle.ACCENT
var max_hp: float = 100.0
var hp: float = 100.0
var attack: float = 20.0
var defense: float = 8.0
var move_speed: float = 120.0
var attack_range: float = 70.0
var attack_interval: float = 1.2
var aggro_range: float = 220.0
var is_player: bool = false
var is_boss: bool = false
var boss_damage_bonus: float = 0.0
var arena_bounds: Rect2 = Rect2(Vector2(48, 48), Vector2(620, 760))
var home_position := Vector2.ZERO
var status_entries: Array = []

var _alive := true
var _attack_cooldown := 0.0

@onready var _collision: CollisionShape2D = $CollisionShape2D
@onready var _health_bar = $HealthBar
@onready var _name_label: Label = $NameLabel
@onready var _status_label: Label = $StatusLabel

func _ready() -> void:
	home_position = global_position
	_ensure_collision_shape()
	_apply_label_theme()
	refresh_visuals()

func setup_actor(config: Dictionary) -> void:
	display_name = str(config.get("display_name", display_name))
	body_color = config.get("body_color", body_color)
	max_hp = float(config.get("max_hp", max_hp))
	hp = max_hp
	attack = float(config.get("attack", attack))
	defense = float(config.get("defense", defense))
	move_speed = float(config.get("move_speed", move_speed))
	attack_range = float(config.get("attack_range", attack_range))
	attack_interval = float(config.get("attack_interval", attack_interval))
	aggro_range = float(config.get("aggro_range", aggro_range))
	is_player = bool(config.get("is_player", is_player))
	is_boss = bool(config.get("is_boss", is_boss))
	boss_damage_bonus = float(config.get("boss_damage_bonus", boss_damage_bonus))
	if config.has("arena_bounds"):
		arena_bounds = config.get("arena_bounds")
	home_position = global_position
	if is_node_ready():
		refresh_visuals()

func tick_actor(delta: float) -> void:
	if not _alive:
		return
	_attack_cooldown = max(_attack_cooldown - delta, 0.0)
	_tick_statuses(delta)
	refresh_visuals()

func attack_target(target) -> int:
	if target == null or not target.is_alive() or not can_attack():
		return 0
	_attack_cooldown = attack_interval
	var dealt = target.receive_damage(attack, self)
	emit_signal("attacked", self, target, dealt)
	return dealt

func receive_damage(raw_damage: float, attacker = null) -> int:
	if not _alive:
		return 0
	var effective_damage: float = raw_damage
	if attacker != null and is_boss and attacker.is_player:
		effective_damage *= 1.0 + attacker.boss_damage_bonus / 100.0
	var final_damage: int = max(1, int(round(effective_damage - defense * 0.35)))
	hp = max(hp - final_damage, 0.0)
	if hp <= 0.0:
		_die()
	refresh_visuals()
	return final_damage

func heal(amount: float) -> int:
	if not _alive:
		return 0
	var healed: int = max(1, int(round(amount)))
	hp = min(hp + healed, max_hp)
	refresh_visuals()
	return healed

func add_status(status: Dictionary) -> void:
	if not _alive:
		return
	var normalized: Dictionary = status.duplicate(true)
	normalized["duration"] = float(normalized.get("duration", 0.0))
	normalized["tick_interval"] = float(normalized.get("tick_interval", 0.0))
	normalized["tick_elapsed"] = 0.0
	status_entries.append(normalized)
	emit_signal("combat_event", "%s 获得状态：%s" % [display_name, normalized.get("name", "状态")])
	refresh_visuals()

func can_move() -> bool:
	for entry in status_entries:
		if bool(entry.get("move_locked", false)):
			return false
	return true

func can_attack() -> bool:
	if _attack_cooldown > 0.0:
		return false
	for entry in status_entries:
		if bool(entry.get("attack_locked", false)):
			return false
	return true

func is_alive() -> bool:
	return _alive

func refresh_visuals() -> void:
	if _name_label == null:
		return
	_name_label.text = display_name
	_status_label.text = _status_summary()
	_health_bar.set_values(hp, max_hp, _bar_color())
	queue_redraw()

func _tick_statuses(delta: float) -> void:
	for index in range(status_entries.size() - 1, -1, -1):
		var entry: Dictionary = status_entries[index]
		entry["duration"] = float(entry.get("duration", 0.0)) - delta
		var tick_interval: float = float(entry.get("tick_interval", 0.0))
		entry["tick_elapsed"] = float(entry.get("tick_elapsed", 0.0)) + delta

		if tick_interval > 0.0 and float(entry.get("tick_elapsed", 0.0)) >= tick_interval:
			entry["tick_elapsed"] = 0.0
			match str(entry.get("type", "")):
				"dot":
					var tick_damage: int = max(1, int(round(float(entry.get("power", 1.0)))))
					hp = max(hp - tick_damage, 0.0)
					emit_signal("combat_event", "%s 受到%s %d" % [display_name, entry.get("name", "持续伤害"), tick_damage])
					if hp <= 0.0:
						_die()
				"hot":
					var tick_heal: int = heal(float(entry.get("power", 1.0)))
					emit_signal("combat_event", "%s 恢复%d生命" % [display_name, tick_heal])

		status_entries[index] = entry

		if float(entry.get("duration", 0.0)) <= 0.0:
			status_entries.remove_at(index)

func _status_summary() -> String:
	var names: Array = []
	for entry in status_entries:
		names.append(str(entry.get("name", "")))
	return " | ".join(names)

func _bar_color() -> Color:
	if is_player:
		return ShanhaiStyle.SUCCESS
	return ShanhaiStyle.BOSS if is_boss else ShanhaiStyle.DANGER

func _die() -> void:
	if not _alive:
		return
	_alive = false
	velocity = Vector2.ZERO
	emit_signal("died", self)

func _ensure_collision_shape() -> void:
	var circle := _collision.shape as CircleShape2D
	if circle == null:
		circle = CircleShape2D.new()
		_collision.shape = circle
	circle.radius = 18.0

func _apply_label_theme() -> void:
	_name_label.position = Vector2(-60, -62)
	_name_label.size = Vector2(120, 24)
	_name_label.horizontal_alignment = HORIZONTAL_ALIGNMENT_CENTER
	_name_label.add_theme_color_override("font_color", ShanhaiStyle.INK)
	_name_label.add_theme_font_size_override("font_size", 16)

	_status_label.position = Vector2(-86, -88)
	_status_label.size = Vector2(172, 20)
	_status_label.horizontal_alignment = HORIZONTAL_ALIGNMENT_CENTER
	_status_label.add_theme_color_override("font_color", ShanhaiStyle.ACCENT_SOFT)
	_status_label.add_theme_font_size_override("font_size", 12)

func _draw() -> void:
	var radius := 18.0 if not is_boss else 24.0
	draw_circle(Vector2.ZERO, radius, body_color)
	draw_arc(Vector2.ZERO, radius + 2.0, 0.0, TAU, 40, Color(0, 0, 0, 0.5), 3.0, true)
	if not is_player and _alive:
		draw_arc(Vector2.ZERO, aggro_range, 0.0, TAU, 56, Color(body_color.r, body_color.g, body_color.b, 0.08), 1.0, true)

func clamp_to_arena() -> void:
	global_position.x = clampf(global_position.x, arena_bounds.position.x, arena_bounds.end.x)
	global_position.y = clampf(global_position.y, arena_bounds.position.y, arena_bounds.end.y)
