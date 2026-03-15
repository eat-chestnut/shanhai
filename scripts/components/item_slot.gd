extends PanelContainer
class_name ItemSlot

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var _title_label: Label
var _type_label: Label
var _count_label: Label

func _ready() -> void:
	_build_ui()

func configure(item: Dictionary, count: int = 0, subtitle: String = "") -> void:
	if _title_label == null:
		_build_ui()
	var definition: Dictionary = item.get("definition", item)
	_title_label.text = str(definition.get("name", item.get("item_id", "未知道具")))
	_type_label.text = subtitle if not subtitle.is_empty() else str(definition.get("type", "loot"))
	_count_label.text = "x%d" % max(count, int(item.get("count", 0)))

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	ShanhaiStyle.apply_panel(self)
	custom_minimum_size = Vector2(0, 88)

	var row := HBoxContainer.new()
	row.add_theme_constant_override("separation", 12)
	add_child(row)

	var icon := ColorRect.new()
	icon.custom_minimum_size = Vector2(54, 54)
	icon.color = ShanhaiStyle.ACCENT
	row.add_child(icon)

	var content := VBoxContainer.new()
	content.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	content.add_theme_constant_override("separation", 4)
	row.add_child(content)

	_title_label = Label.new()
	ShanhaiStyle.apply_body(_title_label, false, 20)
	content.add_child(_title_label)

	_type_label = Label.new()
	ShanhaiStyle.apply_body(_type_label, true, 16)
	content.add_child(_type_label)

	_count_label = Label.new()
	ShanhaiStyle.apply_heading(_count_label, 20)
	row.add_child(_count_label)
