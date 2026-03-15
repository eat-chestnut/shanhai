extends Button
class_name DifficultyCard

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var data: Dictionary = {}

func configure(value: Dictionary, selected: bool, unlocked: bool, player_power: int) -> void:
	data = value.duplicate(true)
	disabled = not unlocked
	ShanhaiStyle.apply_button(self, selected)
	var difficulty_id := str(data.get("difficulty_id", ""))
	var recommended_power := int(data.get("recommended_power", 0))
	var state_text := "已达标" if player_power >= recommended_power else "建议提升"
	if not unlocked:
		state_text = "未解锁"
	text = "%s\n建议战力 %d\n%s" % [_difficulty_name(difficulty_id), recommended_power, state_text]

func _difficulty_name(difficulty_id: String) -> String:
	match difficulty_id:
		"easy":
			return "简易"
		"normal":
			return "普通"
		"hard":
			return "险关"
		_:
			return difficulty_id.capitalize()
