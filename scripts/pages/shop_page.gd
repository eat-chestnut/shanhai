extends ScrollContainer
class_name ShopPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var _content: VBoxContainer
var _status_label: Label
var _tab_row: HBoxContainer
var _list_box: VBoxContainer
var _current_shop_type := "common"

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	refresh()

func activate() -> void:
	call_deferred("_load_current_shop")

func refresh() -> void:
	if _content == null:
		return

	_status_label.text = GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else "普通商店与贡献商店都以后端正式库存和货币结果为准。"
	_rebuild_tabs()
	_rebuild_items()

func _rebuild_tabs() -> void:
	for child in _tab_row.get_children():
		child.queue_free()

	for entry in [
		{"shop_type": "common", "label": "普通商店"},
		{"shop_type": "sect", "label": "贡献商店"}
	]:
		var button := Button.new()
		var shop_type := str(entry.get("shop_type", "common"))
		button.text = str(entry.get("label", "商店"))
		ShanhaiStyle.apply_button(button, shop_type == _current_shop_type)
		button.pressed.connect(_on_shop_tab_pressed.bind(shop_type))
		_tab_row.add_child(button)

func _rebuild_items() -> void:
	for child in _list_box.get_children():
		child.queue_free()

	var items := GameData.get_shop_entries(_current_shop_type)
	if items.is_empty():
		var empty := Label.new()
		empty.text = "当前商店暂无商品。"
		ShanhaiStyle.apply_body(empty, true, 18)
		_list_box.add_child(empty)
		return

	for item in items:
		_list_box.add_child(_build_shop_item(item))

func _build_shop_item(item: Dictionary) -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 8)
	panel.add_child(content)

	var title := Label.new()
	title.text = "%s x%d" % [item.get("item_name", item.get("item_id", "商品")), int(item.get("count", 1))]
	ShanhaiStyle.apply_heading(title, 22)
	content.add_child(title)

	var desc := Label.new()
	desc.text = "价格：%s %d\n限购：%d / %d" % [
		_cost_label(str(item.get("cost_type", "gold"))),
		int(item.get("cost_value", 0)),
		int(item.get("bought_count", 0)),
		int(item.get("buy_limit", 0))
	]
	desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(desc, false, 18)
	content.add_child(desc)

	var action := Button.new()
	action.text = "已售罄" if bool(item.get("is_sold_out", false)) else "购买"
	action.disabled = bool(item.get("is_sold_out", false))
	ShanhaiStyle.apply_button(action, not action.disabled)
	action.pressed.connect(_on_buy_pressed.bind(str(item.get("shop_item_id", ""))))
	content.add_child(action)

	return panel

func _cost_label(cost_type: String) -> String:
	match cost_type:
		"contribution":
			return "贡献"
		"jade":
			return "灵玉"
		_:
			return "灵石"

func _on_shop_tab_pressed(shop_type: String) -> void:
	_current_shop_type = shop_type
	refresh()
	call_deferred("_load_current_shop")

func _on_buy_pressed(shop_item_id: String) -> void:
	await GameData.buy_shop_item(_current_shop_type, shop_item_id, 1)

func _load_current_shop() -> void:
	await GameData.load_shop_runtime(_current_shop_type)

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

	var intro := PanelContainer.new()
	ShanhaiStyle.apply_panel(intro, true)
	_content.add_child(intro)

	var intro_box := VBoxContainer.new()
	intro_box.add_theme_constant_override("separation", 8)
	intro.add_child(intro_box)

	var title := Label.new()
	title.text = "宗门商店"
	ShanhaiStyle.apply_title(title, 30)
	intro_box.add_child(title)

	_status_label = Label.new()
	_status_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_status_label, false, 18)
	intro_box.add_child(_status_label)

	_tab_row = HBoxContainer.new()
	_tab_row.add_theme_constant_override("separation", 12)
	_content.add_child(_tab_row)

	_list_box = VBoxContainer.new()
	_list_box.add_theme_constant_override("separation", 12)
	_content.add_child(_list_box)
