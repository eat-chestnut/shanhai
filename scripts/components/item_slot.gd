extends PanelContainer
class_name ItemSlot

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var _title_label: Label
var _type_label: Label
var _count_label: Label
var _icon: ColorRect
var _background: ColorRect

func _ready() -> void:
	_build_ui()

func configure(item: Dictionary, count: int = 0, subtitle: String = "") -> void:
	if _title_label == null:
		_build_ui()
	var definition: Dictionary = item.get("definition", item)
	_title_label.text = str(definition.get("name", item.get("item_id", "未知道具")))
	_type_label.text = subtitle if not subtitle.is_empty() else str(definition.get("type", "loot"))
	_count_label.text = "x%d" % max(count, int(item.get("count", 0)))
	
	# 应用稀有度样式
	var rarity: String = definition.get("rarity", "common")
	_apply_rarity_style(rarity)

func _apply_rarity_style(rarity: String) -> void:
	var rarity_config = _get_rarity_config(rarity)
	if rarity_config.is_empty():
		return
	
	# 应用背景颜色
	if _background:
		_background.color = Color.from_string(rarity_config.get("bg_color", "#2F2F2F"), Color.WHITE)
	
	# 应用边框颜色
	self.add_theme_stylebox_override("panel", _create_rarity_border(rarity_config))
	
	# 应用文字颜色
	if _title_label:
		_title_label.modulate = Color.from_string(rarity_config.get("text_color", "#FFFFFF"), Color.WHITE)
	if _type_label:
		_type_label.modulate = Color.from_string(rarity_config.get("text_color", "#FFFFFF"), Color.WHITE)

func _get_rarity_config(rarity: String) -> Dictionary:
	var config = GameData.get_rarity_config(rarity)
	if not config.is_empty():
		return config
	
	# 如果没有找到配置，返回默认配置
	match rarity:
		"common":
			return {
				"text_color": "#FFFFFF",
				"bg_color": "#2F2F2F", 
				"border_color": "#7A7A7A"
			}
		"uncommon":
			return {
				"text_color": "#D8F3FF",
				"bg_color": "#163A59",
				"border_color": "#4DB3FF"
			}
		"rare":
			return {
				"text_color": "#D8F3FF",
				"bg_color": "#163A59",
				"border_color": "#4DB3FF"
			}
		"epic":
			return {
				"text_color": "#F3E2FF",
				"bg_color": "#4A235A",
				"border_color": "#C56CFF"
			}
		"legendary":
			return {
				"text_color": "#FFE8D6",
				"bg_color": "#6B4423",
				"border_color": "#FFB84D"
			}
		_:
			return {}

func _create_rarity_border(config: Dictionary) -> StyleBoxFlat:
	var border_color = Color.from_string(config.get("border_color", "#7A7A7A"), Color.WHITE)
	var style = StyleBoxFlat.new()
	style.bg_color = Color.TRANSPARENT
	style.border_width_left = 2
	style.border_width_top = 2
	style.border_width_right = 2
	style.border_width_bottom = 2
	style.border_color = border_color
	style.corner_radius_top_left = 4
	style.corner_radius_top_right = 4
	style.corner_radius_bottom_left = 4
	style.corner_radius_bottom_right = 4
	return style

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	# 创建背景
	_background = ColorRect.new()
	_background.color = Color.from_string("#2F2F2F", Color.WHITE)
	_background.set_anchors_and_offsets_preset(Control.PRESET_FULL_RECT)
	add_child(_background)

	ShanhaiStyle.apply_panel(self)
	custom_minimum_size = Vector2(0, 88)

	var row := HBoxContainer.new()
	row.add_theme_constant_override("separation", 12)
	add_child(row)

	_icon = ColorRect.new()
	_icon.custom_minimum_size = Vector2(54, 54)
	_icon.color = ShanhaiStyle.ACCENT
	row.add_child(_icon)

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
