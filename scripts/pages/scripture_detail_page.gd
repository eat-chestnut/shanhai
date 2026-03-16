extends ScrollContainer
class_name ScriptureDetailPage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")
const ITEM_SLOT_SCENE := preload("res://scenes/components/item_slot.tscn")

signal return_list
signal start_battle

var _content: VBoxContainer
var _status_label: Label
var _world_level_box: FlowContainer
var _summary_label: Label
var _tier_overview_box: VBoxContainer
var _monster_box: VBoxContainer
var _drop_box: VBoxContainer
var _upgrade_box: VBoxContainer
var _battle_button: Button
var _upgrade_button: Button

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	UiState.selection_changed.connect(_on_selection_changed)
	refresh()

func activate() -> void:
	call_deferred("_load_detail")

func refresh() -> void:
	if _content == null:
		return

	var scripture_id := _resolve_scripture_id()
	if scripture_id.is_empty():
		_render_empty_state()
		return

	var list_entry := _resolve_scripture_entry(scripture_id)
	var detail := GameData.get_scripture_detail(scripture_id)
	var scripture := GameData.get_scripture(scripture_id)
	var is_unlocked := bool(list_entry.get("is_unlocked", false))

	_sync_selected_world_level(detail)

	var selected_world_level := int(UiState.selection.get("world_level", 0))
	var selected_tier := GameData.get_scripture_tier(scripture_id, selected_world_level)
	var next_upgrade := _next_upgrade_entry(detail)

	_status_label.text = _status_text(list_entry)
	_summary_label.text = _summary_text(scripture, list_entry, detail, selected_tier)
	_rebuild_world_level_buttons(detail)
	_rebuild_tier_overview(detail.get("tier_preview", []), selected_world_level)
	_rebuild_monster_preview(selected_tier)
	_rebuild_drop_preview(selected_tier)
	_rebuild_upgrade_preview(next_upgrade)

	_battle_button.disabled = not is_unlocked or selected_tier.is_empty() or selected_world_level <= 0
	_upgrade_button.disabled = not is_unlocked or next_upgrade.is_empty()
	_upgrade_button.text = "升级到世界等级 %d" % int(next_upgrade.get("target_world_level", 0)) if not next_upgrade.is_empty() else "当前暂无下一档升级"

func _on_selection_changed() -> void:
	refresh()
	call_deferred("_load_detail")

func _load_detail() -> void:
	var scripture_id := _resolve_scripture_id()
	if scripture_id.is_empty():
		return
	await GameData.load_scripture_detail(scripture_id)

func _resolve_scripture_id() -> String:
	var scripture_id := str(UiState.selection.get("scripture_id", ""))
	if not scripture_id.is_empty():
		return scripture_id
	var entries := GameData.get_scripture_entries()
	if entries.is_empty():
		return ""
	scripture_id = str(entries[0].get("scripture_id", ""))
	if not scripture_id.is_empty():
		UiState.set_selection("scripture_id", scripture_id)
	return scripture_id

func _resolve_scripture_entry(scripture_id: String) -> Dictionary:
	for entry in GameData.get_scripture_entries():
		if str(entry.get("scripture_id", "")) == scripture_id:
			return entry.duplicate(true)
	return {}

func _sync_selected_world_level(detail: Dictionary) -> void:
	var available_levels: Array = detail.get("available_world_levels", [])
	if available_levels.is_empty():
		if int(UiState.selection.get("world_level", 0)) != 0:
			UiState.set_selection("world_level", 0)
		return

	var selected_world_level := int(UiState.selection.get("world_level", 0))
	if available_levels.has(selected_world_level):
		return

	var current_world_level := int(detail.get("current_world_level", 0))
	UiState.set_selection("world_level", current_world_level if available_levels.has(current_world_level) else int(available_levels[0]))

func _status_text(list_entry: Dictionary) -> String:
	if not GameData.last_runtime_error.is_empty():
		return GameData.last_runtime_error
	if bool(list_entry.get("is_unlocked", false)):
		return "经卷详情与升级消耗以后端正式运行态返回为准。"
	return str(list_entry.get("unlock_text", "经卷未解锁"))

func _summary_text(scripture: Dictionary, list_entry: Dictionary, detail: Dictionary, selected_tier: Dictionary) -> String:
	var selected_world_level := int(UiState.selection.get("world_level", 0))
	return "%s  ·  %s\n当前强度：%d\n已解锁最高强度：%d\n可选择历史等级：%s\n当前查看：%s\n阶段说明：%s" % [
		scripture.get("scripture_name", list_entry.get("scripture_name", "经卷")),
		scripture.get("scripture_group", list_entry.get("scripture_group", "")),
		int(detail.get("current_world_level", list_entry.get("current_world_level", 0))),
		int(detail.get("max_unlocked_world_level", list_entry.get("max_unlocked_world_level", 0))),
		_levels_text(detail.get("available_world_levels", [])),
		("世界等级 %d" % selected_world_level) if selected_world_level > 0 else "尚未解锁",
		selected_tier.get("new_feature_note", "当前阶段暂无额外说明")
	]

func _rebuild_world_level_buttons(detail: Dictionary) -> void:
	for child in _world_level_box.get_children():
		child.queue_free()

	var available_levels: Array = detail.get("available_world_levels", [])
	if available_levels.is_empty():
		var empty := Label.new()
		empty.text = "尚未解锁任何可进入的历史等级。"
		ShanhaiStyle.apply_body(empty, true, 16)
		_world_level_box.add_child(empty)
		return

	for level in available_levels:
		var button := Button.new()
		var world_level := int(level)
		button.text = "世界等级 %d" % world_level
		ShanhaiStyle.apply_button(button, world_level == int(UiState.selection.get("world_level", 0)))
		button.pressed.connect(func() -> void:
			UiState.set_selection("world_level", world_level)
		)
		_world_level_box.add_child(button)

func _rebuild_tier_overview(tier_preview: Array, selected_world_level: int) -> void:
	for child in _tier_overview_box.get_children():
		child.queue_free()

	if tier_preview.is_empty():
		var empty := Label.new()
		empty.text = "当前暂无阶段配置。"
		ShanhaiStyle.apply_body(empty, true, 16)
		_tier_overview_box.add_child(empty)
		return

	for tier in tier_preview:
		var panel := PanelContainer.new()
		var is_selected := selected_world_level >= int(tier.get("world_level_start", 0)) and selected_world_level <= int(tier.get("world_level_end", 0))
		ShanhaiStyle.apply_panel(panel, is_selected)
		_tier_overview_box.add_child(panel)

		var content := VBoxContainer.new()
		content.add_theme_constant_override("separation", 6)
		panel.add_child(content)

		var title := Label.new()
		title.text = "世界等级 %d - %d" % [int(tier.get("world_level_start", 0)), int(tier.get("world_level_end", 0))]
		ShanhaiStyle.apply_heading(title, 20)
		content.add_child(title)

		var desc := Label.new()
		desc.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
		desc.text = "阶段说明：%s\n属性倍率：HP x%.2f / ATK x%.2f / DEF x%.2f\n掉落倍率：x%.2f  金币倍率：x%.2f" % [
			tier.get("new_feature_note", ""),
			float(tier.get("hp_scale", 1.0)),
			float(tier.get("atk_scale", 1.0)),
			float(tier.get("def_scale", 1.0)),
			float(tier.get("reward_scale", 1.0)),
			float(tier.get("gold_scale", 1.0))
		]
		ShanhaiStyle.apply_body(desc, false, 16)
		content.add_child(desc)

func _rebuild_monster_preview(selected_tier: Dictionary) -> void:
	for child in _monster_box.get_children():
		child.queue_free()

	if selected_tier.is_empty():
		var empty := Label.new()
		empty.text = "当前阶段暂无怪物池可展示。"
		ShanhaiStyle.apply_body(empty, true, 16)
		_monster_box.add_child(empty)
		return

	_monster_box.add_child(_build_monster_group("普通怪物", selected_tier.get("normal_monster_ids", [])))
	_monster_box.add_child(_build_monster_group("精英怪物", selected_tier.get("elite_monster_ids", [])))
	_monster_box.add_child(_build_monster_group("Boss 池", selected_tier.get("boss_monster_ids", [])))

func _build_monster_group(title_text: String, monster_ids: Array) -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 6)
	panel.add_child(content)

	var title := Label.new()
	title.text = title_text
	ShanhaiStyle.apply_heading(title, 20)
	content.add_child(title)

	if monster_ids.is_empty():
		var empty := Label.new()
		empty.text = "当前为空。"
		ShanhaiStyle.apply_body(empty, true, 16)
		content.add_child(empty)
		return panel

	for monster_id in monster_ids:
		var monster := GameData.get_scripture_monster(str(monster_id))
		var label := Label.new()
		label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
		label.text = "%s  ·  %s / %s / %s" % [
			monster.get("name", monster_id),
			monster.get("monster_type", ""),
			monster.get("race", ""),
			monster.get("rarity", "")
		]
		ShanhaiStyle.apply_body(label, false, 16)
		content.add_child(label)

	return panel

func _rebuild_drop_preview(selected_tier: Dictionary) -> void:
	for child in _drop_box.get_children():
		child.queue_free()

	if selected_tier.is_empty():
		var empty := Label.new()
		empty.text = "当前阶段暂无掉落组可展示。"
		ShanhaiStyle.apply_body(empty, true, 16)
		_drop_box.add_child(empty)
		return

	var extra_drop_tags: Array = selected_tier.get("extra_drop_tags", [])
	if extra_drop_tags.is_empty():
		var none := Label.new()
		none.text = "当前阶段未配置额外掉落标签。"
		ShanhaiStyle.apply_body(none, true, 16)
		_drop_box.add_child(none)
		return

	for drop_tag in extra_drop_tags:
		_drop_box.add_child(_build_drop_tag_card(str(drop_tag)))

func _build_drop_tag_card(drop_tag: String) -> Control:
	var panel := PanelContainer.new()
	ShanhaiStyle.apply_panel(panel)

	var content := VBoxContainer.new()
	content.add_theme_constant_override("separation", 8)
	panel.add_child(content)

	var tag := GameData.get_scripture_drop_tag(drop_tag)
	var title := Label.new()
	title.text = str(tag.get("tag_name", drop_tag))
	ShanhaiStyle.apply_heading(title, 18)
	content.add_child(title)

	if tag.is_empty():
		var empty := Label.new()
		empty.text = "当前仅存在正式标识，未找到对应掉落组明细。"
		ShanhaiStyle.apply_body(empty, true, 16)
		content.add_child(empty)
		return panel

	var flow := FlowContainer.new()
	flow.add_theme_constant_override("h_separation", 10)
	flow.add_theme_constant_override("v_separation", 10)
	content.add_child(flow)

	for entry in tag.get("items", []):
		var item_id := str(entry.get("item_id", ""))
		var slot = ITEM_SLOT_SCENE.instantiate()
		slot.custom_minimum_size = Vector2(240, 88)
		slot.configure(
			{
				"definition": GameData.get_item_definition(item_id),
				"item_id": item_id
			},
			int(entry.get("max", 0)),
			"掉落 %d-%d · 权重 %d" % [
				int(entry.get("min", 0)),
				int(entry.get("max", 0)),
				int(entry.get("weight", 0))
			]
		)
		flow.add_child(slot)

	return panel

func _rebuild_upgrade_preview(next_upgrade: Dictionary) -> void:
	for child in _upgrade_box.get_children():
		child.queue_free()

	if next_upgrade.is_empty():
		var empty := Label.new()
		empty.text = "当前暂无下一档升级配置。"
		ShanhaiStyle.apply_body(empty, true, 16)
		_upgrade_box.add_child(empty)
		return

	var summary := Label.new()
	summary.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	summary.text = "目标世界等级：%d\n所需玩家等级：Lv.%d\n金币消耗：%d" % [
		int(next_upgrade.get("target_world_level", 0)),
		int(next_upgrade.get("required_player_level", 0)),
		int(next_upgrade.get("cost_gold", 0))
	]
	ShanhaiStyle.apply_body(summary, false, 16)
	_upgrade_box.add_child(summary)

	var flow := FlowContainer.new()
	flow.add_theme_constant_override("h_separation", 10)
	flow.add_theme_constant_override("v_separation", 10)
	_upgrade_box.add_child(flow)

	for cost_item in next_upgrade.get("cost_items", []):
		var item_id := str(cost_item.get("item_id", ""))
		var required_count := int(cost_item.get("count", 0))
		var owned_count := PlayerState.get_item_count(item_id)
		var slot = ITEM_SLOT_SCENE.instantiate()
		slot.custom_minimum_size = Vector2(240, 88)
		slot.configure(
			{
				"definition": GameData.get_item_definition(item_id),
				"item_id": item_id
			},
			required_count,
			"背包 %d / 需要 %d" % [owned_count, required_count]
		)
		flow.add_child(slot)

	var gold_slot = ITEM_SLOT_SCENE.instantiate()
	gold_slot.custom_minimum_size = Vector2(240, 88)
	gold_slot.configure(
		{
			"definition": GameData.get_item_definition("gold"),
			"item_id": "gold"
		},
		int(next_upgrade.get("cost_gold", 0)),
		"持有 %d / 需要 %d" % [PlayerState.get_gold(), int(next_upgrade.get("cost_gold", 0))]
	)
	flow.add_child(gold_slot)

func _next_upgrade_entry(detail: Dictionary) -> Dictionary:
	var entries: Array = detail.get("upgrade_cost_preview", [])
	if entries.is_empty():
		return {}
	return entries[0].duplicate(true)

func _levels_text(levels: Array) -> String:
	if levels.is_empty():
		return "暂无"
	return ", ".join(levels.map(func(level: int) -> String: return str(level)))

func _on_start_pressed() -> void:
	var scripture_id := _resolve_scripture_id()
	if scripture_id.is_empty():
		return
	var world_level := int(UiState.selection.get("world_level", 0))
	var selected_tier := GameData.get_scripture_tier(scripture_id, world_level)
	if selected_tier.is_empty() or world_level <= 0:
		return
	var scripture := GameData.get_scripture(scripture_id)
	BattleState.start_scripture(
		{
			"scripture_id": scripture_id,
			"scripture_name": scripture.get("scripture_name", scripture_id),
			"scripture_group": scripture.get("scripture_group", "")
		},
		world_level,
		selected_tier
	)
	emit_signal("start_battle")

func _on_upgrade_pressed() -> void:
	var scripture_id := _resolve_scripture_id()
	var next_upgrade := _next_upgrade_entry(GameData.get_scripture_detail(scripture_id))
	if scripture_id.is_empty() or next_upgrade.is_empty():
		return
	if await GameData.upgrade_scripture(scripture_id, int(next_upgrade.get("target_world_level", 0))):
		UiState.set_selection("world_level", int(next_upgrade.get("target_world_level", 0)))

func _render_empty_state() -> void:
	_status_label.text = "当前暂无经卷可查看。"
	_summary_label.text = "请先完成经卷配置或刷新运行态数据。"
	_rebuild_world_level_buttons({})
	_rebuild_tier_overview([], 0)
	_rebuild_monster_preview({})
	_rebuild_drop_preview({})
	_rebuild_upgrade_preview({})
	_battle_button.disabled = true
	_upgrade_button.disabled = true
	_upgrade_button.text = "当前暂无下一档升级"

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
	title.text = "经卷详情"
	ShanhaiStyle.apply_title(title, 30)
	intro_box.add_child(title)

	_status_label = Label.new()
	_status_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_status_label, false, 18)
	intro_box.add_child(_status_label)

	_summary_label = Label.new()
	_summary_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_summary_label, false, 18)
	_content.add_child(_summary_label)

	var button_row := HBoxContainer.new()
	button_row.add_theme_constant_override("separation", 12)
	_content.add_child(button_row)

	var back_button := Button.new()
	back_button.text = "返回经卷列表"
	ShanhaiStyle.apply_button(back_button)
	back_button.pressed.connect(func() -> void:
		emit_signal("return_list")
	)
	button_row.add_child(back_button)

	_battle_button = Button.new()
	_battle_button.text = "进入本次经卷战斗"
	ShanhaiStyle.apply_button(_battle_button, true)
	_battle_button.pressed.connect(_on_start_pressed)
	button_row.add_child(_battle_button)

	_upgrade_button = Button.new()
	_upgrade_button.text = "当前暂无下一档升级"
	ShanhaiStyle.apply_button(_upgrade_button)
	_upgrade_button.pressed.connect(_on_upgrade_pressed)
	button_row.add_child(_upgrade_button)

	var level_heading := Label.new()
	level_heading.text = "历史等级选择"
	ShanhaiStyle.apply_heading(level_heading, 22)
	_content.add_child(level_heading)

	_world_level_box = FlowContainer.new()
	_world_level_box.add_theme_constant_override("h_separation", 10)
	_world_level_box.add_theme_constant_override("v_separation", 10)
	_content.add_child(_world_level_box)

	var tier_heading := Label.new()
	tier_heading.text = "阶段总览"
	ShanhaiStyle.apply_heading(tier_heading, 22)
	_content.add_child(tier_heading)

	_tier_overview_box = VBoxContainer.new()
	_tier_overview_box.add_theme_constant_override("separation", 10)
	_content.add_child(_tier_overview_box)

	var monster_heading := Label.new()
	monster_heading.text = "怪物标签"
	ShanhaiStyle.apply_heading(monster_heading, 22)
	_content.add_child(monster_heading)

	_monster_box = VBoxContainer.new()
	_monster_box.add_theme_constant_override("separation", 10)
	_content.add_child(_monster_box)

	var drop_heading := Label.new()
	drop_heading.text = "掉落标签"
	ShanhaiStyle.apply_heading(drop_heading, 22)
	_content.add_child(drop_heading)

	_drop_box = VBoxContainer.new()
	_drop_box.add_theme_constant_override("separation", 10)
	_content.add_child(_drop_box)

	var upgrade_heading := Label.new()
	upgrade_heading.text = "升级消耗预览"
	ShanhaiStyle.apply_heading(upgrade_heading, 22)
	_content.add_child(upgrade_heading)

	_upgrade_box = VBoxContainer.new()
	_upgrade_box.add_theme_constant_override("separation", 10)
	_content.add_child(_upgrade_box)
