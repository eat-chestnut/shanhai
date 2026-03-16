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

func activate() -> void:
	call_deferred("_load_runtime_equipment")

func refresh() -> void:
	if _content == null:
		return

	for child in _content.get_children():
		child.queue_free()

	_content.add_child(_build_player_summary())
	_content.add_child(_build_skill_section())
	_content.add_child(_build_equipment_section())
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
	desc.text = "%s  Lv.%d\n生命 %d  攻击 %d  防御 %d  Boss增伤 %d%%\n%s上限 %d  技能点 %d\n灵石 %d  灵玉 %d  贡献 %d" % [
		GameData.get_character_class_name(str(PlayerState.player.get("class_id", ""))),
		PlayerState.get_level(),
		int(stats.get("max_hp", 0)),
		int(stats.get("atk", 0)),
		int(stats.get("def", 0)),
		int(stats.get("boss_dmg", 0)),
		PlayerState.get_resource_name(),
		PlayerState.get_max_energy(),
		PlayerState.get_skill_points(),
		PlayerState.get_gold(),
		PlayerState.get_jade(),
		PlayerState.get_contribution()
	]
	desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(desc, false, 18)
	content.add_child(desc)

	var status := Label.new()
	status.text = GameData.last_runtime_error if not GameData.last_runtime_error.is_empty() else "装备与成长操作会优先走后端正式接口。"
	status.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(status, true, 16)
	content.add_child(status)

	return panel

func _build_skill_section() -> Control:
	var section := VBoxContainer.new()
	section.add_theme_constant_override("separation", 10)

	var heading := Label.new()
	heading.text = "职业技能"
	ShanhaiStyle.apply_heading(heading, 24)
	section.add_child(heading)

	var runtime_skills := PlayerState.get_runtime_skills()
	if runtime_skills.is_empty():
		var empty := Label.new()
		empty.text = "当前职业暂无技能。"
		ShanhaiStyle.apply_body(empty, true, 18)
		section.add_child(empty)
		return section

	for skill in runtime_skills:
		var panel := PanelContainer.new()
		ShanhaiStyle.apply_panel(panel)
		section.add_child(panel)

		var content := VBoxContainer.new()
		content.add_theme_constant_override("separation", 8)
		panel.add_child(content)

		var title := Label.new()
		title.text = "%s  Lv.%d  [%s]" % [
			skill.get("skill_name", skill.get("skill_id", "技能")),
			int(skill.get("skill_level", 1)),
			"主动" if str(skill.get("type", "")) == "active" else "被动"
		]
		ShanhaiStyle.apply_heading(title, 20)
		content.add_child(title)

		var desc := Label.new()
		desc.text = "%s\n%s" % [
			str(skill.get("skill_desc", "暂无说明")),
			_skill_meta_text(skill)
		]
		desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
		ShanhaiStyle.apply_body(desc, false, 16)
		content.add_child(desc)

		var upgrade_button := Button.new()
		upgrade_button.text = "升级技能"
		upgrade_button.disabled = not PlayerState.can_upgrade_skill(str(skill.get("skill_id", "")))
		ShanhaiStyle.apply_button(upgrade_button, not upgrade_button.disabled)
		upgrade_button.pressed.connect(_on_upgrade_skill.bind(str(skill.get("skill_id", ""))))
		content.add_child(upgrade_button)

	return section

func _build_equipment_section() -> Control:
	var section := VBoxContainer.new()
	section.add_theme_constant_override("separation", 10)

	var heading := Label.new()
	heading.text = "装备成长"
	ShanhaiStyle.apply_heading(heading, 24)
	section.add_child(heading)

	var runtime_entries := GameData.get_equipment_runtime_entries()
	if runtime_entries.is_empty():
		var equipped := PlayerState.get_equipped_item_ids()
		for slot_index in equipped.size():
			var slot = EQUIPMENT_SLOT_SCENE.instantiate()
			var equip_id := str(equipped[slot_index])
			slot.configure("装备槽 %d" % (slot_index + 1), GameData.get_equipment(equip_id))
			section.add_child(slot)

		var fallback := Label.new()
		fallback.text = "当前仍在使用本地兜底装备摘要，进入正式运行态后会显示装备实例与成长操作。"
		fallback.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
		ShanhaiStyle.apply_body(fallback, true, 16)
		section.add_child(fallback)
		return section

	for entry in runtime_entries:
		section.add_child(_build_runtime_equipment_card(entry))

	var set_summary: Array = []
	var raw_set_summary = GameData.runtime_equipment_detail.get("set_summary", [])
	if raw_set_summary is Array:
		set_summary = raw_set_summary
	if not set_summary.is_empty():
		var set_panel := PanelContainer.new()
		ShanhaiStyle.apply_panel(set_panel, true)
		section.add_child(set_panel)

		var set_box := VBoxContainer.new()
		set_box.add_theme_constant_override("separation", 6)
		set_panel.add_child(set_box)

		var set_heading := Label.new()
		set_heading.text = "套装激活摘要"
		ShanhaiStyle.apply_heading(set_heading, 20)
		set_box.add_child(set_heading)

		for set_count in set_summary:
			var set_data := GameData.get_set(str(set_count.get("set_id", "")))
			var label := Label.new()
			label.text = "%s：已穿戴 %d 件" % [
				set_data.get("set_id", set_count.get("set_id", "套装")),
				int(set_count.get("equipped_count", 0))
			]
			ShanhaiStyle.apply_body(label, false, 16)
			set_box.add_child(label)

	return section

func _build_runtime_equipment_card(entry: Dictionary) -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 8)
	panel.add_child(content)

	var title := Label.new()
	title.text = "%s  [%s]%s" % [
		entry.get("name", entry.get("equip_id", "装备")),
		entry.get("slot_type", entry.get("type", "slot")),
		"  已穿戴" if bool(entry.get("is_equipped", false)) else ""
	]
	ShanhaiStyle.apply_heading(title, 22)
	content.add_child(title)

	var detail := Label.new()
	detail.text = "实例 %s\n星级 %d  等级 %d\n攻击 %d  防御 %d  Boss增伤 %d%%\n宝石：%s\n蓝词条：%s\n紫洗练：%s" % [
		entry.get("equipment_uid", ""),
		int(entry.get("star_level", 0)),
		int(entry.get("level", 1)),
		int(entry.get("final_atk", entry.get("base_atk", 0))),
		int(entry.get("final_def", entry.get("base_def", 0))),
		int(entry.get("bonus_boss_dmg", 0)),
		_runtime_gem_text(entry.get("gem_slots", [])),
		_runtime_affix_text(entry.get("blue_affix", null), "未提取"),
		_runtime_affix_text(entry.get("purple_refinement", null), "未洗练")
	]
	detail.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(detail, false, 17)
	content.add_child(detail)

	var actions := HBoxContainer.new()
	actions.add_theme_constant_override("separation", 10)
	content.add_child(actions)

	var equip_button := Button.new()
	equip_button.text = "卸下" if bool(entry.get("is_equipped", false)) else "穿戴"
	ShanhaiStyle.apply_button(equip_button, true)
	equip_button.pressed.connect(_on_equipment_action.bind("unequip" if bool(entry.get("is_equipped", false)) else "equip", {"equipment_uid": str(entry.get("equipment_uid", ""))}))
	actions.add_child(equip_button)

	var star_cost := int(entry.get("star_level", 0)) + 1
	var star_button := Button.new()
	star_button.text = "升星 (%d)" % star_cost
	star_button.disabled = _inventory_count("material_star_stone") < star_cost
	ShanhaiStyle.apply_button(star_button, not star_button.disabled)
	star_button.pressed.connect(_on_equipment_action.bind("star_up", {"equipment_uid": str(entry.get("equipment_uid", ""))}))
	actions.add_child(star_button)

	var socket_candidate := _find_socket_candidate(entry)
	var socket_button := Button.new()
	socket_button.text = "镶嵌宝石"
	socket_button.disabled = socket_candidate.is_empty()
	ShanhaiStyle.apply_button(socket_button, not socket_button.disabled)
	socket_button.pressed.connect(_on_equipment_action.bind("socket_gem", {
		"equipment_uid": str(entry.get("equipment_uid", "")),
		"gem_id": str(socket_candidate.get("gem_id", "")),
		"slot_index": int(socket_candidate.get("slot_index", 0))
	}))
	actions.add_child(socket_button)

	var refine_row := HBoxContainer.new()
	refine_row.add_theme_constant_override("separation", 10)
	content.add_child(refine_row)

	var blue_button := Button.new()
	blue_button.text = "提取蓝词条"
	blue_button.disabled = _inventory_count("material_seal_essence") <= 0
	ShanhaiStyle.apply_button(blue_button, not blue_button.disabled)
	blue_button.pressed.connect(_on_equipment_action.bind("extract_blue_affix", {"equipment_uid": str(entry.get("equipment_uid", ""))}))
	refine_row.add_child(blue_button)

	var purple_button := Button.new()
	purple_button.text = "紫洗练"
	purple_button.disabled = _inventory_count("material_refine_sand") <= 0
	ShanhaiStyle.apply_button(purple_button, not purple_button.disabled)
	purple_button.pressed.connect(_on_equipment_action.bind("refine_purple_affix", {"equipment_uid": str(entry.get("equipment_uid", ""))}))
	refine_row.add_child(purple_button)

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

func _runtime_gem_text(gem_slots: Array) -> String:
	if gem_slots.is_empty():
		return "未镶嵌"
	var labels: Array = []
	for slot in gem_slots:
		var gem_id := str(slot.get("gem_id", ""))
		var slot_type := str(slot.get("slot_type", "attribute"))
		if gem_id.is_empty():
			labels.append("%s孔位: 空" % ("核心" if slot_type == "boss_core" else "属性"))
			continue
		var gem := GameData.get_gem(gem_id)
		labels.append("%s孔位: %s" % [
			"核心" if slot_type == "boss_core" else "属性",
			gem.get("name", gem_id)
		])
	return " / ".join(labels)

func _runtime_affix_text(payload: Variant, empty_text: String) -> String:
	if payload is Dictionary and not payload.is_empty():
		return str(payload.get("name", empty_text))
	return empty_text

func _inventory_count(item_id: String) -> int:
	for entry in PlayerState.get_inventory_entries():
		if str(entry.get("item_id", "")) == item_id:
			return int(entry.get("count", 0))
	return 0

func _find_socket_candidate(entry: Dictionary) -> Dictionary:
	var gem_slots: Array = entry.get("gem_slots", [])
	for slot in gem_slots:
		var slot_type := str(slot.get("slot_type", "attribute"))
		for item_entry in PlayerState.get_inventory_entries():
			var item_id := str(item_entry.get("item_id", ""))
			var gem_data := GameData.get_gem(item_id)
			if gem_data.is_empty():
				continue
			var gem_type := "boss_core" if str(gem_data.get("type", "")) == "boss_core" else "attribute"
			if gem_type == slot_type:
				return {
					"gem_id": item_id,
					"slot_index": int(slot.get("slot_index", 0))
				}
	return {}

func _skill_meta_text(skill: Dictionary) -> String:
	var lines: Array = []
	if str(skill.get("type", "")) == "active":
		lines.append("冷却 %ss  消耗 %d%s" % [
			str(skill.get("cooldown", 0)),
			int(skill.get("cost", 0)),
			PlayerState.get_resource_name()
		])
		lines.append("技能强度 %d  持续 %ds" % [int(skill.get("scaled_power", 0)), int(skill.get("duration", 0))])
	else:
		lines.append("被动效果：%s" % [JSON.stringify(skill.get("stat_bonuses", {})) if not skill.get("stat_bonuses", {}).is_empty() else "触发型被动"])
	if not skill.get("effect_payload", {}).is_empty():
		lines.append("扩展：%s" % JSON.stringify(skill.get("effect_payload", {})))
	return "\n".join(lines)

func _on_upgrade_skill(skill_id: String) -> void:
	PlayerState.upgrade_skill(skill_id)

func _on_equipment_action(action: String, payload: Dictionary) -> void:
	await GameData.run_equipment_action(action, payload)

func _load_runtime_equipment() -> void:
	await GameData.load_equipment_runtime_detail()

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
