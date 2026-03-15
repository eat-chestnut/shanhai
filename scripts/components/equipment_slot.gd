extends PanelContainer
class_name EquipmentSlot

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var _slot_label: Label
var _name_label: Label
var _detail_label: Label

func _ready() -> void:
	_build_ui()

func configure(slot_name: String, equip_data: Dictionary) -> void:
	if _slot_label == null:
		_build_ui()
	_slot_label.text = slot_name
	if equip_data.is_empty():
		_name_label.text = "未装备"
		_detail_label.text = "等待挂载"
		return

	_name_label.text = str(equip_data.get("name", "未命名装备"))
	_detail_label.text = "Lv.%d  攻 %d  防 %d" % [
		int(equip_data.get("level", 1)),
		int(equip_data.get("base_atk", 0)),
		int(equip_data.get("base_def", 0))
	]

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	ShanhaiStyle.apply_panel(self, true)
	custom_minimum_size = Vector2(0, 104)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 4)
	add_child(content)

	_slot_label = Label.new()
	ShanhaiStyle.apply_heading(_slot_label, 18)
	content.add_child(_slot_label)

	_name_label = Label.new()
	ShanhaiStyle.apply_body(_name_label, false, 22)
	content.add_child(_name_label)

	_detail_label = Label.new()
	ShanhaiStyle.apply_body(_detail_label, true, 16)
	content.add_child(_detail_label)
