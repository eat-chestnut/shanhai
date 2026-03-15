extends Node2D
class_name CircularHealthBar

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var current_value: float = 100.0
var max_value: float = 100.0
var bar_color: Color = ShanhaiStyle.SUCCESS
var radius: float = 20.0
var width: float = 4.0

func set_values(current_hp: float, max_hp: float, color: Color = ShanhaiStyle.SUCCESS) -> void:
	current_value = max(current_hp, 0.0)
	max_value = max(max_hp, 1.0)
	bar_color = color
	queue_redraw()

func _draw() -> void:
	draw_arc(Vector2.ZERO, radius, 0.0, TAU, 48, Color(0, 0, 0, 0.35), width + 1.0, true)
	var progress: float = clampf(current_value / max_value, 0.0, 1.0)
	draw_arc(Vector2.ZERO, radius, -PI * 0.5, -PI * 0.5 + TAU * progress, 48, bar_color, width, true)
