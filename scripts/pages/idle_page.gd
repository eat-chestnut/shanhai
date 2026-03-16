extends ScrollContainer
class_name IdlePage

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")
const ITEM_SLOT_SCENE := preload("res://scenes/components/item_slot.tscn")

var _content: VBoxContainer
var _status_label: Label
var _claim_button: Button
var _reward_box: VBoxContainer

func _ready() -> void:
	_build_ui()
	GameData.changed.connect(refresh)
	refresh()

func activate() -> void:
	call_deferred("_load_idle_runtime")

func refresh() -> void:
	if _content == null:
		return

	var status := GameData.get_idle_status()
	var rule: Dictionary = status.get("rule", {})
	var claimable_seconds := int(status.get("claimable_seconds", 0))
	var cap_seconds := int(status.get("cap_seconds", 0))
	_status_label.text = "%s\n累计时长 %s / %s\n%s" % [
		str(status.get("source_hint", "闭关收益会沿用当前主线与副本成长材料循环。")),
		_format_duration(claimable_seconds),
		_format_duration(cap_seconds),
		str(rule.get("bonus_hint", "收益不会直接产出高阶装备和宝石。"))
	]
	_claim_button.disabled = claimable_seconds <= 0

	for child in _reward_box.get_children():
		child.queue_free()

	var rewards: Array = status.get("rewards", [])
	if rewards.is_empty():
		var empty := Label.new()
		empty.text = "当前暂无可领取收益。"
		ShanhaiStyle.apply_body(empty, true, 18)
		_reward_box.add_child(empty)
		return

	for reward in rewards:
		var slot = ITEM_SLOT_SCENE.instantiate()
		slot.custom_minimum_size = Vector2(240, 88)
		slot.configure(reward, int(reward.get("count", 0)))
		_reward_box.add_child(slot)

func _on_claim_pressed() -> void:
	await GameData.claim_idle_rewards()

func _load_idle_runtime() -> void:
	await GameData.load_idle_runtime()

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
	title.text = "闭关收益"
	ShanhaiStyle.apply_title(title, 30)
	intro_box.add_child(title)

	_status_label = Label.new()
	_status_label.autowrap_mode = TextServer.AUTOWRAP_WORD_SMART
	ShanhaiStyle.apply_body(_status_label, false, 18)
	intro_box.add_child(_status_label)

	_claim_button = Button.new()
	_claim_button.text = "领取闭关收益"
	ShanhaiStyle.apply_button(_claim_button, true)
	_claim_button.pressed.connect(_on_claim_pressed)
	_content.add_child(_claim_button)

	_reward_box = VBoxContainer.new()
	_reward_box.add_theme_constant_override("separation", 10)
	_content.add_child(_reward_box)

func _format_duration(total_seconds: int) -> String:
	var seconds: int = max(total_seconds, 0)
	var hours: int = seconds / 3600
	var minutes: int = (seconds % 3600) / 60
	return "%02d时%02d分" % [hours, minutes]
