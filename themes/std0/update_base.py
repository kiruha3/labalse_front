#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт обновления base.css под дизайн-систему Минюста.
СОХРАНЯЕТ кодировку cp1251!
Принцип: сдержанность, без ИИ-скруглений, солидный гос. вид.
"""

import shutil
import sys

# === ЦВЕТОВАЯ КАРТА ===
# Формат: (старое_значение, новое_значение, комментарий)
COLOR_MAP = [
    # Шапка страницы — тёмно-зелёный градиент вместо жёлтого
    ("background-color : #fec ;", "background : linear-gradient(180deg, #123d20 0%, #1a5c33 100%) ;"),
    ("background-color : #fec;", "background : linear-gradient(180deg, #123d20 0%, #1a5c33 100%) ;"),

    # Заголовки таблиц — зелёный вместо тёмно-серого
    ("background-color : #404040 ;", "background-color : #1a5c33 ;"),
    ("background-color : #404040;", "background-color : #1a5c33 ;"),

    # Чередующиеся строки — чистый белый и тёплый серый
    ("background-color : #f0f0f0 ;", "background-color : #ffffff ;"),
    ("background-color : #f0f0f0;", "background-color : #ffffff ;"),
    ("background-color : #e0e0e0 ;", "background-color : #f4f1ec ;"),
    ("background-color : #e0e0e0;", "background-color : #f4f1ec ;"),

    # Инфо-строки — светло-зелёный вместо голубого
    ("background-color : #e0e0ff ;", "background-color : #edf5f0 ;"),
    ("background-color : #e0e0ff;", "background-color : #edf5f0 ;"),

    # Границы — тёплый серый вместо холодного
    ("border : 1px solid #808080 ;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #808080;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #c0c0c0 ;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #c0c0c0;", "border : 1px solid #c9c3bb ;"),

    # Ссылки в таблицах — зелёный вместо синего
    ("color : #0080ff ;", "color : #1a5c33 ;"),
    ("color : #0080ff;", "color : #1a5c33 ;"),
    ("color : #ff8000 ;", "color : #b85c3e ;"),  # hover ссылок — тёмный коралл
    ("color : #ff8000;", "color : #b85c3e ;"),

    # Меню — тёмный фон, белый текст
    ("color : #000000 ;", "color : #ffffff ;"),  # mhMenu a
    ("color : #000000;", "color : #ffffff ;"),

    # Инфо-панель — зелёный вместо жёлтого
    ("background-color : #fc4 ;", "background-color : #1a5c33 ;"),
    ("background-color : #fc4;", "background-color : #1a5c33 ;"),

    # Диалоги — чистый серый вместо грязного
    ("background-color : #ddd ;", "background-color : #f4f1ec ;"),
    ("background-color : #ddd;", "background-color : #f4f1ec ;"),

    # Шапка диалога — зелёный вместо голубого
    ("background-color : #00c0ff ;", "background-color : #1a5c33 ;"),
    ("background-color : #00c0ff;", "background-color : #1a5c33 ;"),

    # Календарь — зелёный вместо синего
    ("background-color : #0080ff ;", "background-color : #1a5c33 ;"),

    # Hover на даты календаря — тёплый вместо жёлтого
    ("background-color : #fef0c0 ;", "background-color : #e8f0ec ;"),
    ("background-color : #fef0c0;", "background-color : #e8f0ec ;"),

    # Сегодня в календаре — мягкий зелёный вместо яркого
    ("background-color : #c0ffc0 ;", "background-color : #d4e8db ;"),
    ("background-color : #c0ffc0;", "background-color : #d4e8db ;"),

    # Выходные в календаре — тёмно-зелёный вместо красного
    ("background-color : #ff0000 ;", "background-color : #8b2e2e ;"),
    ("background-color : #ff0000;", "background-color : #8b2e2e ;"),

    # Цвет текста выходных — светлый
    ("color : #ff0000 ;", "color : #8b2e2e ;"),
    ("color : #ff0000;", "color : #8b2e2e ;"),

    # iframe-стиль — тёплый вместо оранжевого
    ("background-color : #fff0e0 ;", "background-color : #f8f5f0 ;"),
    ("background-color : #fff0e0;", "background-color : #f8f5f0 ;"),
    ("border : 1px dashed #807060 ;", "border : 1px dashed #a09888 ;"),

    # Отладочная инфо — тёплый hover
    ("background-color : #fec ;", "background-color : #f0ebe3 ;"),

    # Текст mhInfo — светлый на тёмном
    ("color : #ffffff ;", "color : #ffffff ;"),  # already white, keep

    # Ссылки в подтаблице — светлые на тёмном
    ("color : #000000 ;", "color : #e8e4de ;"),  # mhiSubTable a
]

# Отдельные замены (строки, не цвета)
TEXT_REPLACEMENTS = [
    # Шрифт — более современный
    ('font-family : "verdana" , "arial" , sans-serif ;',
     'font-family : "Segoe UI", "Helvetica Neue", Arial, sans-serif ;'),

    # Заголовок организации — крупнее и светлее
    ('font-size : 14pt ;', 'font-size : 16pt ;'),
    ('font-size : 12pt ;', 'font-size : 13pt ;'),

    # Меню — крупнее для читаемости
    ('font-size : 8pt ; font-weight : normal ; text-align : center ;',
     'font-size : 9pt ; font-weight : 500 ; text-align : center ;'),
]

def update_file(filepath):
    """Обновить CSS-файл с сохранением cp1251."""
    # Читаем в cp1251
    with open(filepath, 'r', encoding='cp1251', errors='replace') as f:
        content = f.read()

    original = content

    # Применяем цветовые замены
    for old, new in COLOR_MAP:
        content = content.replace(old, new)

    # Применяем текстовые замены
    for old, new in TEXT_REPLACEMENTS:
        content = content.replace(old, new)

    # Пишем обратно в cp1251
    with open(filepath, 'w', encoding='cp1251', errors='replace') as f:
        f.write(content)

    changes = sum(1 for old, _ in COLOR_MAP if old in original)
    return changes

if __name__ == '__main__':
    filepath = sys.argv[1] if len(sys.argv) > 1 else 'base.css'
    changes = update_file(filepath)
    print(f"Updated {filepath}: {changes} color replacements applied")
