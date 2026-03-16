extends Node

signal screen_changed(screen: String)
signal title_changed(title: String)
signal selection_changed

const SCREEN_CLASS_SELECT := "class_select"
const SCREEN_HALL := "hall"
const SCREEN_MAINLINE := "mainline"
const SCREEN_DUNGEON := "dungeon"
const SCREEN_TASK := "task"
const SCREEN_SHOP := "shop"
const SCREEN_INVENTORY := "inventory"
const SCREEN_IDLE := "idle"
const SCREEN_CHALLENGE := "challenge"
const SCREEN_BATTLE := "battle"
const SCREEN_BATTLE_RESULT := "battle_result"

var current_screen := SCREEN_CLASS_SELECT
var current_title := "选定命格"
var selection := {
	"chapter_id": "",
	"node_id": "",
	"difficulty_id": "",
	"dungeon_id": ""
}

func navigate_to(screen: String, payload: Dictionary = {}) -> void:
	current_screen = screen
	for key in payload.keys():
		selection[key] = payload[key]
	_update_title()
	emit_signal("screen_changed", current_screen)
	emit_signal("selection_changed")

func set_selection(key: String, value: Variant) -> void:
	if selection.get(key) == value:
		return
	selection[key] = value
	emit_signal("selection_changed")

func should_show_navigation() -> bool:
	return current_screen != SCREEN_CLASS_SELECT and current_screen != SCREEN_BATTLE

func _update_title() -> void:
	match current_screen:
		SCREEN_CLASS_SELECT:
			current_title = "选定命格"
		SCREEN_HALL:
			current_title = "山海宗门大厅"
		SCREEN_MAINLINE:
			current_title = "主线巡厄"
		SCREEN_DUNGEON:
			current_title = "宗门副本"
		SCREEN_TASK:
			current_title = "宗门任务"
		SCREEN_SHOP:
			current_title = "宗门商店"
		SCREEN_INVENTORY:
			current_title = "行囊与装备"
		SCREEN_IDLE:
			current_title = "闭关收益"
		SCREEN_CHALLENGE:
			current_title = "玄渊试炼塔"
		SCREEN_BATTLE:
			current_title = "巡厄战斗"
		SCREEN_BATTLE_RESULT:
			current_title = "战斗结算"
		_:
			current_title = "山海巡厄录"
	emit_signal("title_changed", current_title)
