extends Control
class_name AppRoot

const ShanhaiStyle = preload("res://scripts/core/shanhai_style.gd")

const TOP_BAR_SCENE := preload("res://scenes/components/top_bar.tscn")
const BOTTOM_NAV_SCENE := preload("res://scenes/components/bottom_nav.tscn")
const PAGE_SCENES := {
	UiState.SCREEN_CLASS_SELECT: preload("res://scenes/pages/class_select_page.tscn"),
	UiState.SCREEN_HALL: preload("res://scenes/pages/hall_page.tscn"),
	UiState.SCREEN_MAINLINE: preload("res://scenes/pages/mainline_page.tscn"),
	UiState.SCREEN_SCRIPTURE_LIST: preload("res://scenes/pages/scripture_list_page.tscn"),
	UiState.SCREEN_SCRIPTURE_DETAIL: preload("res://scenes/pages/scripture_detail_page.tscn"),
	UiState.SCREEN_DUNGEON: preload("res://scenes/pages/dungeon_page.tscn"),
	UiState.SCREEN_TASK: preload("res://scenes/pages/task_page.tscn"),
	UiState.SCREEN_SHOP: preload("res://scenes/pages/shop_page.tscn"),
	UiState.SCREEN_INVENTORY: preload("res://scenes/pages/inventory_page.tscn"),
	UiState.SCREEN_IDLE: preload("res://scenes/pages/idle_page.tscn"),
	UiState.SCREEN_CHALLENGE: preload("res://scenes/pages/challenge_page.tscn"),
	UiState.SCREEN_BATTLE: preload("res://scenes/pages/battle_page.tscn"),
	UiState.SCREEN_BATTLE_RESULT: preload("res://scenes/pages/battle_result_page.tscn")
}

var _content_host: Control
var _bottom_nav
var _loading_label: Label
var _pages: Dictionary = {}

func _ready() -> void:
	_build_ui()
	_connect_global_signals()
	await GameData.load_all()
	_instantiate_pages()
	_switch_screen(UiState.current_screen)
	_loading_label.visible = false

func _draw() -> void:
	draw_rect(Rect2(Vector2.ZERO, size), ShanhaiStyle.BG, true)
	draw_circle(Vector2(size.x * 0.2, size.y * 0.1), 180.0, Color(ShanhaiStyle.ACCENT.r, ShanhaiStyle.ACCENT.g, ShanhaiStyle.ACCENT.b, 0.08))
	draw_circle(Vector2(size.x * 0.85, size.y * 0.22), 210.0, Color(ShanhaiStyle.ACCENT_SOFT.r, ShanhaiStyle.ACCENT_SOFT.g, ShanhaiStyle.ACCENT_SOFT.b, 0.05))
	draw_arc(Vector2(size.x * 0.52, size.y * 0.78), 280.0, PI * 0.1, PI * 0.92, 64, Color(ShanhaiStyle.ACCENT.r, ShanhaiStyle.ACCENT.g, ShanhaiStyle.ACCENT.b, 0.12), 2.0, true)

func _notification(what: int) -> void:
	if what == NOTIFICATION_RESIZED:
		queue_redraw()

func _build_ui() -> void:
	anchor_right = 1.0
	anchor_bottom = 1.0

	var margin := MarginContainer.new()
	margin.anchor_right = 1.0
	margin.anchor_bottom = 1.0
	margin.add_theme_constant_override("margin_left", 18)
	margin.add_theme_constant_override("margin_top", 18)
	margin.add_theme_constant_override("margin_right", 18)
	margin.add_theme_constant_override("margin_bottom", 18)
	add_child(margin)

	var root := VBoxContainer.new()
	root.anchor_right = 1.0
	root.anchor_bottom = 1.0
	root.size_flags_vertical = Control.SIZE_EXPAND_FILL
	root.add_theme_constant_override("separation", 14)
	margin.add_child(root)

	var top_bar = TOP_BAR_SCENE.instantiate()
	root.add_child(top_bar)

	_content_host = Control.new()
	_content_host.size_flags_vertical = Control.SIZE_EXPAND_FILL
	root.add_child(_content_host)

	_bottom_nav = BOTTOM_NAV_SCENE.instantiate()
	root.add_child(_bottom_nav)

	_loading_label = Label.new()
	_loading_label.text = "正在连通后端与本地引导数据..."
	_loading_label.horizontal_alignment = HORIZONTAL_ALIGNMENT_CENTER
	_loading_label.vertical_alignment = VERTICAL_ALIGNMENT_CENTER
	_loading_label.anchor_right = 1.0
	_loading_label.anchor_bottom = 1.0
	ShanhaiStyle.apply_body(_loading_label, false, 22)
	_content_host.add_child(_loading_label)

func _connect_global_signals() -> void:
	_bottom_nav.navigate.connect(func(screen: String) -> void:
		UiState.navigate_to(screen)
	)
	UiState.screen_changed.connect(_switch_screen)
	GameApi.request_log.connect(func(message: String) -> void:
		print(message)
	)

func _instantiate_pages() -> void:
	if not _pages.is_empty():
		return

	for screen in PAGE_SCENES.keys():
		var page = PAGE_SCENES[screen].instantiate()
		page.anchor_right = 1.0
		page.anchor_bottom = 1.0
		page.visible = false
		_content_host.add_child(page)
		_pages[screen] = page

	var class_page = _pages[UiState.SCREEN_CLASS_SELECT]
	class_page.class_confirmed.connect(_on_class_confirmed)

	var hall_page = _pages[UiState.SCREEN_HALL]
	hall_page.navigate.connect(func(screen: String) -> void:
		UiState.navigate_to(screen)
	)

	var mainline_page = _pages[UiState.SCREEN_MAINLINE]
	mainline_page.start_battle.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_BATTLE)
	)

	var scripture_list_page = _pages[UiState.SCREEN_SCRIPTURE_LIST]
	scripture_list_page.open_detail.connect(func(scripture_id: String) -> void:
		UiState.navigate_to(UiState.SCREEN_SCRIPTURE_DETAIL, {"scripture_id": scripture_id})
	)

	var scripture_detail_page = _pages[UiState.SCREEN_SCRIPTURE_DETAIL]
	scripture_detail_page.return_list.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_SCRIPTURE_LIST)
	)
	scripture_detail_page.start_battle.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_BATTLE)
	)

	var dungeon_page = _pages[UiState.SCREEN_DUNGEON]
	dungeon_page.start_battle.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_BATTLE)
	)

	var challenge_page = _pages[UiState.SCREEN_CHALLENGE]
	challenge_page.start_battle.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_BATTLE)
	)

	var battle_page = _pages[UiState.SCREEN_BATTLE]
	battle_page.battle_finished.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_BATTLE_RESULT)
	)

	var result_page = _pages[UiState.SCREEN_BATTLE_RESULT]
	result_page.return_hall.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_HALL)
	)
	result_page.rechallenge.connect(func() -> void:
		UiState.navigate_to(UiState.SCREEN_BATTLE)
	)

func _switch_screen(screen: String) -> void:
	if _pages.is_empty():
		return

	for key in _pages.keys():
		var page: Control = _pages[key]
		var is_active: bool = key == screen
		page.visible = is_active
		if is_active:
			if page.has_method("refresh"):
				page.call("refresh")
			if page.has_method("activate"):
				page.call("activate")
		elif page.has_method("deactivate"):
			page.call("deactivate")

func _on_class_confirmed(class_id: String) -> void:
	await GameData.commit_class_selection(class_id)
	UiState.navigate_to(UiState.SCREEN_HALL)
