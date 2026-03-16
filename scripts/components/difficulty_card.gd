extends Button
class_name DifficultyCard

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var data: Dictionary = {}

func configure(value: Dictionary, selected: bool, unlocked: bool, player_power: int) -> void:
	data = value.duplicate(true)
	disabled = not unlocked
	ShanhaiStyle.apply_button(self, selected)
	var difficulty_id := str(data.get("difficulty_id", ""))
	var difficulty_name := str(data.get("difficulty_name", _difficulty_name(difficulty_id)))
	var recommended_power := int(data.get("recommended_power", 0))
	var state_text := str(data.get("recommendation_text", "已达标" if player_power >= recommended_power else "建议提升"))
	if not unlocked:
		state_text = "未解锁"
	elif bool(data.get("is_first_clear", false)):
		state_text = "%s  ·  首通已完成" % state_text
	text = "%s%s\n建议战力 %d\n%s" % [
		difficulty_name,
		" / %s" % str(data.get("tier_label", "")) if str(data.get("tier_label", "")) != "" else "",
		recommended_power,
		state_text
	]

func _difficulty_name(difficulty_id: String) -> String:
	match difficulty_id:
		"easy":
			return "简单"
		"normal":
			return "普通"
		"hard":
			return "困难"
		"nightmare":
			return "梦魇"
		_:
			return difficulty_id.capitalize()
