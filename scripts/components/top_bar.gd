extends PanelContainer
class_name TopBar

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

var _title_label: Label
var _stats_label: Label
var _currency_label: Label

func _ready() -> void:
	_build_ui()
	PlayerState.changed.connect(refresh)
	UiState.title_changed.connect(_on_title_changed)
	GameData.changed.connect(refresh)
	refresh()

func refresh() -> void:
	if _title_label == null:
		return
	var stats := PlayerState.get_total_stats()
	_title_label.text = UiState.current_title
	_stats_label.text = "%s  Lv.%d  %s  战力 %d" % [
		GameData.get_character_class_name(str(PlayerState.player.get("class_id", ""))),
		PlayerState.get_level(),
		PlayerState.get_player_name(),
		int(stats.get("power", 0))
	]
	_currency_label.text = "灵石 %d  灵玉 %d  贡献 %d  技能点 %d" % [
		PlayerState.get_gold(),
		PlayerState.get_jade(),
		PlayerState.get_contribution(),
		PlayerState.get_skill_points()
	]

func _build_ui() -> void:
	if get_child_count() > 0:
		return

	ShanhaiStyle.apply_panel(self, true)
	size_flags_horizontal = Control.SIZE_EXPAND_FILL

	var root := VBoxContainer.new()
	root.add_theme_constant_override("separation", 10)
	add_child(root)

	_title_label = Label.new()
	ShanhaiStyle.apply_title(_title_label, 32)
	root.add_child(_title_label)

	var row := HBoxContainer.new()
	row.alignment = BoxContainer.ALIGNMENT_BEGIN
	row.add_theme_constant_override("separation", 16)
	root.add_child(row)

	_stats_label = Label.new()
	ShanhaiStyle.apply_body(_stats_label, false, 18)
	_stats_label.size_flags_horizontal = Control.SIZE_EXPAND_FILL
	row.add_child(_stats_label)

	_currency_label = Label.new()
	ShanhaiStyle.apply_heading(_currency_label, 20)
	row.add_child(_currency_label)

func _on_title_changed(_title: String) -> void:
	refresh()
