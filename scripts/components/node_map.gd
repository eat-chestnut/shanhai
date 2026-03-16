extends PanelContainer
class_name NodeMap

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

signal node_selected(node: Dictionary)

var _list: HBoxContainer

func _ready() -> void:
	_build_ui()

func configure(nodes: Array, selected_node_id: String) -> void:
	if _list == null:
		return

	for child in _list.get_children():
		child.queue_free()

	for index in nodes.size():
		var node: Dictionary = nodes[index]
		var button := Button.new()
		var payload := node.duplicate(true)
		var is_unlocked := bool(node.get("is_unlocked", true))
		var progress_state := str(node.get("progress_state", "available"))
		button.text = "%s%s" % [
			str(node.get("node_name", "未命名节点")),
			"\n%s" % _progress_text(progress_state)
		]
		button.disabled = not is_unlocked
		button.size_flags_horizontal = Control.SIZE_EXPAND_FILL
		var is_selected := str(node.get("node_id", "")) == selected_node_id
		ShanhaiStyle.apply_button(button, is_selected)
		button.pressed.connect(_on_node_pressed.bind(payload))
		_list.add_child(button)

		if index < nodes.size() - 1:
			var arrow := Label.new()
			arrow.text = ">>>"
			ShanhaiStyle.apply_body(arrow, true, 18)
			_list.add_child(arrow)

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	ShanhaiStyle.apply_panel(self, true)
	var root := VBoxContainer.new()
	root.add_theme_constant_override("separation", 12)
	add_child(root)

	var heading := Label.new()
	heading.text = "节点图"
	ShanhaiStyle.apply_heading(heading, 22)
	root.add_child(heading)

	_list = HBoxContainer.new()
	_list.add_theme_constant_override("separation", 8)
	root.add_child(_list)

func _on_node_pressed(node: Dictionary) -> void:
	emit_signal("node_selected", node)

func _progress_text(progress_state: String) -> String:
	match progress_state:
		"cleared":
			return "已通关"
		"current":
			return "当前推进"
		"available":
			return "已解锁"
		_:
			return "未解锁"
