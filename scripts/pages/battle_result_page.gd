extends ScrollContainer
class_name BattleResultPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal return_hall
signal rechallenge

const ITEM_SLOT_SCENE := preload("res://scenes/components/item_slot.tscn")

var _content: VBoxContainer

func _ready() -> void:
	_build_ui()
	BattleState.result_ready.connect(refresh)
	refresh(BattleState.last_result)

func refresh(_result: Dictionary = {}) -> void:
	if _content == null:
		return
	for child in _content.get_children():
		child.queue_free()

	var result := BattleState.last_result
	if result.is_empty():
		var empty := Label.new()
		empty.text = "尚无战斗结算。"
		ShanhaiStyle.apply_body(empty, true, 18)
		_content.add_child(empty)
		return

	var header := PanelContainer.new()
	ShanhaiStyle.apply_panel(header, true)
	_content.add_child(header)

	var header_box := VBoxContainer.new()
	header_box.add_theme_constant_override("separation", 8)
	header.add_child(header_box)

	var title := Label.new()
	title.text = "巡厄告捷" if bool(result.get("victory", false)) else "本次巡厄失利"
	ShanhaiStyle.apply_title(title, 34)
	header_box.add_child(title)

	var context: Dictionary = result.get("context", {})
	var summary := Label.new()
	summary.text = "%s\n耗时 %.1f 秒\n%s" % [
		_context_name(context),
		float(result.get("elapsed_seconds", 0.0)),
		"首通奖励已发放。" if bool(result.get("first_clear", false)) else "已按常规掉落结算。"
	]
	summary.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(summary, false, 18)
	header_box.add_child(summary)

	var rewards_heading := Label.new()
	rewards_heading.text = "奖励入包"
	ShanhaiStyle.apply_heading(rewards_heading, 24)
	_content.add_child(rewards_heading)

	var rewards: Array = result.get("rewards", [])
	if rewards.is_empty():
		var none := Label.new()
		none.text = "本次没有获得额外奖励。"
		ShanhaiStyle.apply_body(none, true, 18)
		_content.add_child(none)
	else:
		for reward in rewards:
			var slot = ITEM_SLOT_SCENE.instantiate()
			slot.configure(reward, int(reward.get("count", 0)))
			_content.add_child(slot)

	var buttons := HBoxContainer.new()
	buttons.add_theme_constant_override("separation", 12)
	_content.add_child(buttons)

	var hall_button := Button.new()
	hall_button.text = "返回大厅"
	ShanhaiStyle.apply_button(hall_button)
	hall_button.pressed.connect(func() -> void:
		emit_signal("return_hall")
	)
	buttons.add_child(hall_button)

	var retry_button := Button.new()
	retry_button.text = "再次挑战"
	ShanhaiStyle.apply_button(retry_button, true)
	retry_button.pressed.connect(func() -> void:
		emit_signal("rechallenge")
	)
	buttons.add_child(retry_button)

func _context_name(context: Dictionary) -> String:
	if str(context.get("mode", "")) == "mainline":
		return "%s / %s / %s" % [context.get("chapter_name", "主线"), context.get("node_name", "节点"), context.get("difficulty_id", "")]
	return "%s / %s" % [context.get("dungeon_name", "副本"), context.get("difficulty_id", "")]

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	var margin := MarginContainer.new()
	margin.add_theme_constant_override("margin_left", 28)
	margin.add_theme_constant_override("margin_top", 18)
	margin.add_theme_constant_override("margin_right", 28)
	margin.add_theme_constant_override("margin_bottom", 18)
	add_child(margin)

	_content = VBoxContainer.new()
	_content.add_theme_constant_override("separation", 18)
	margin.add_child(_content)
