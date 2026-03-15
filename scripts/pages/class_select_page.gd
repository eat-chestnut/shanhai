extends ScrollContainer
class_name ClassSelectPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal class_confirmed(class_id: String)

var _content: VBoxContainer

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	PlayerState.changed.connect(refresh)
	refresh()

func refresh() -> void:
	if _content == null:
		return

	for child in _content.get_children():
		if child.name != "Header":
			child.queue_free()

	for class_data in GameData.character_classes:
		var class_id := str(class_data.get("class_id", ""))
		var card := PanelContainer.new()
		ShanhaiStyle.apply_panel(card, class_id == str(PlayerState.player.get("class_id", "")))
		_content.add_child(card)

		var body := VBoxContainer.new()
		body.add_theme_constant_override("separation", 10)
		card.add_child(body)

		var title := Label.new()
		title.text = "%s  [%s]" % [class_data.get("class_name", "职业"), class_data.get("role_type", "unknown")]
		ShanhaiStyle.apply_heading(title, 26)
		body.add_child(title)

		var desc := Label.new()
		desc.text = str(class_data.get("class_desc", ""))
		desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
		ShanhaiStyle.apply_body(desc, false, 18)
		body.add_child(desc)

		var button := Button.new()
		var is_open := bool(class_data.get("is_open", false))
		button.text = "进入山门" if is_open else "暂未开放"
		button.disabled = not is_open
		ShanhaiStyle.apply_button(button, is_open)
		button.pressed.connect(_on_class_pressed.bind(class_id))
		body.add_child(button)

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	size_flags_vertical = Control.SIZE_EXPAND_FILL
	var margin := MarginContainer.new()
	margin.add_theme_constant_override("margin_left", 32)
	margin.add_theme_constant_override("margin_top", 20)
	margin.add_theme_constant_override("margin_right", 32)
	margin.add_theme_constant_override("margin_bottom", 20)
	add_child(margin)

	_content = VBoxContainer.new()
	_content.name = "Content"
	_content.add_theme_constant_override("separation", 18)
	margin.add_child(_content)

	var header := VBoxContainer.new()
	header.name = "Header"
	header.add_theme_constant_override("separation", 8)
	_content.add_child(header)

	var title := Label.new()
	title.text = "择一命格，启程巡厄"
	ShanhaiStyle.apply_title(title, 40)
	header.add_child(title)

	var body := Label.new()
	body.text = "首版客户端仅开放金刚，其他职业保留入口与后续扩展位。"
	body.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(body, true, 18)
	header.add_child(body)

func _on_class_pressed(class_id: String) -> void:
	emit_signal("class_confirmed", class_id)
