extends ScrollContainer
class_name InventoryPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

const INVENTORY_SLOT_SCENE := preload("res://scenes/components/inventory_slot.tscn")
const EQUIPMENT_SLOT_SCENE := preload("res://scenes/components/equipment_slot.tscn")

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
		child.queue_free()

	_content.add_child(_build_player_summary())
	_content.add_child(_build_equipment_section())
	_content.add_child(_build_enhancement_section())
	_content.add_child(_build_inventory_section())

func _build_player_summary() -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel, true)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 8)
	panel.add_child(content)

	var title := Label.new()
	title.text = "角色总览"
	ShanhaiStyle.apply_heading(title, 24)
	content.add_child(title)

	var stats := PlayerState.get_total_stats()
	var desc := Label.new()
	desc.text = "%s  Lv.%d\n生命 %d  攻击 %d  防御 %d  Boss增伤 %d%%" % [
		GameData.get_character_class_name(str(PlayerState.player.get("class_id", ""))),
		PlayerState.get_level(),
		int(stats.get("max_hp", 0)),
		int(stats.get("atk", 0)),
		int(stats.get("def", 0)),
		int(stats.get("boss_dmg", 0))
	]
	desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(desc, false, 18)
	content.add_child(desc)

	return panel

func _build_equipment_section() -> Control:
	var section := VBoxContainer.new()
	section.add_theme_constant_override("separation", 10)

	var heading := Label.new()
	heading.text = "装备与套装"
	ShanhaiStyle.apply_heading(heading, 24)
	section.add_child(heading)

	var equipped := PlayerState.get_equipped_item_ids()
	for slot_index in equipped.size():
		var slot = EQUIPMENT_SLOT_SCENE.instantiate()
		var equip_id := str(equipped[slot_index])
		slot.configure("装备槽 %d" % (slot_index + 1), GameData.get_equipment(equip_id))
		section.add_child(slot)

	for set_count in PlayerState.get_equipment_summary().get("set_counts", []):
		var label := Label.new()
		var set_data := GameData.get_set(str(set_count.get("set_id", "")))
		label.text = "%s：已穿戴 %d 件" % [
			set_data.get("set_id", "套装"),
			int(set_count.get("equipped_count", 0))
		]
		ShanhaiStyle.apply_body(label, true, 16)
		section.add_child(label)

	return section

func _build_enhancement_section() -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 10)
	panel.add_child(content)

	var heading := Label.new()
	heading.text = "宝石 / 蓝词条 / 紫洗练"
	ShanhaiStyle.apply_heading(heading, 24)
	content.add_child(heading)

	var gem_line := Label.new()
	gem_line.text = "宝石：%s" % _names_from_ids(PlayerState.get_equipped_gem_ids(), func(id: String) -> Dictionary: return GameData.get_gem(id))
	ShanhaiStyle.apply_body(gem_line, false, 18)
	content.add_child(gem_line)

	var affix_line := Label.new()
	affix_line.text = "蓝词条：%s" % _names_from_ids(PlayerState.get_blue_affix_ids(), func(id: String) -> Dictionary: return GameData.get_blue_affix(id))
	ShanhaiStyle.apply_body(affix_line, false, 18)
	content.add_child(affix_line)

	var refinement_line := Label.new()
	refinement_line.text = "紫洗练：%s" % _names_from_ids(PlayerState.get_purple_refinement_ids(), func(id: String) -> Dictionary: return GameData.get_purple_refinement(id))
	ShanhaiStyle.apply_body(refinement_line, false, 18)
	content.add_child(refinement_line)

	return panel

func _build_inventory_section() -> Control:
	var section := VBoxContainer.new()
	section.add_theme_constant_override("separation", 10)

	var heading := Label.new()
	heading.text = "背包"
	ShanhaiStyle.apply_heading(heading, 24)
	section.add_child(heading)

	var entries := PlayerState.get_inventory_entries()
	if entries.is_empty():
		var empty := Label.new()
		empty.text = "当前背包为空。"
		ShanhaiStyle.apply_body(empty, true, 18)
		section.add_child(empty)
		return section

	for entry in entries:
		var slot = INVENTORY_SLOT_SCENE.instantiate()
		slot.configure(entry, int(entry.get("count", 0)))
		section.add_child(slot)

	return section

func _names_from_ids(ids: Array, resolver: Callable) -> String:
	var names: Array = []
	for raw_id in ids:
		var data: Dictionary = resolver.call(str(raw_id))
		names.append(str(data.get("name", raw_id)))
	return ", ".join(names) if not names.is_empty() else "未挂载"

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
