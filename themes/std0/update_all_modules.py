#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Универсальный скрипт обновления цветов во всех CSS-файлах проекта.
СОХРАНЯЕТ кодировку cp1251.
"""

import os
import sys

# Карта замен цветов (старая строка → новая строка)
# Порядок ВАЖЕН — более специфичные замены должны идти раньше общих
COLOR_MAP = [
    # Специфичные (чтобы не конфликтовали с общими)
    ("background-color : #fec ;", "background : linear-gradient(180deg, #123d20 0%, #1a5c33 100%) ;"),
    ("background-color : #fec;", "background : linear-gradient(180deg, #123d20 0%, #1a5c33 100%) ;"),

    # Заголовки таблиц
    ("background-color : #404040 ;", "background-color : #1a5c33 ;"),
    ("background-color : #404040;", "background-color : #1a5c33 ;"),

    # Чередование строк
    ("background-color : #f0f0f0 ;", "background-color : #ffffff ;"),
    ("background-color : #f0f0f0;", "background-color : #ffffff ;"),
    ("background-color : #e0e0e0 ;", "background-color : #f4f1ec ;"),
    ("background-color : #e0e0e0;", "background-color : #f4f1ec ;"),

    # Голубые акценты → зелёные
    ("background-color : #e0e0ff ;", "background-color : #edf5f0 ;"),
    ("background-color : #e0e0ff;", "background-color : #edf5f0 ;"),
    ("background-color : #00c0ff ;", "background-color : #1a5c33 ;"),
    ("background-color : #00c0ff;", "background-color : #1a5c33 ;"),

    # Синие ссылки → зелёные
    ("color : #0080ff ;", "color : #1a5c33 ;"),
    ("color : #0080ff;", "color : #1a5c33 ;"),
    ("color : #0000ff ;", "color : #1a5c33 ;"),
    ("color : #0000ff;", "color : #1a5c33 ;"),

    # Оранжевые/красные hover → коралловый
    ("color : #ff8000 ;", "color : #b85c3e ;"),
    ("color : #ff8000;", "color : #b85c3e ;"),
    ("color : #ff0000 ;", "color : #8b2e2e ;"),
    ("color : #ff0000;", "color : #8b2e2e ;"),

    # Жёлтые/оранжевые фоны
    ("background-color : #fc4 ;", "background-color : #1a5c33 ;"),
    ("background-color : #fc4;", "background-color : #1a5c33 ;"),
    ("background-color : #fec ;", "background-color : #f0ebe3 ;"),
    ("background-color : #fef0c0 ;", "background-color : #e8f0ec ;"),
    ("background-color : #fff0e0 ;", "background-color : #f8f5f0 ;"),
    ("background-color : #c0ffc0 ;", "background-color : #d4e8db ;"),

    # Тёмные фоны
    ("background-color : #333 ;", "background-color : #1a5c33 ;"),
    ("background-color : #333;", "background-color : #1a5c33 ;"),
    ("background-color : #222 ;", "background-color : #123d20 ;"),
    ("background-color : #222;", "background-color : #123d20 ;"),

    # Границы
    ("border : 1px solid #808080 ;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #808080;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #c0c0c0 ;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #c0c0c0;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #ccc ;", "border : 1px solid #c9c3bb ;"),
    ("border : 1px solid #ccc;", "border : 1px solid #c9c3bb ;"),
    ("border: 1px solid #c0c0c0", "border: 1px solid #c9c3bb"),
    ("border: 1px solid #ccc", "border: 1px solid #c9c3bb"),

    # Серые фоны → тёплые
    ("background-color : #ddd ;", "background-color : #f4f1ec ;"),
    ("background-color : #ddd;", "background-color : #f4f1ec ;"),
    ("background-color : #ccc ;", "background-color : #e8e4de ;"),
    ("background-color : #ccc;", "background-color : #e8e4de ;"),
    ("background-color : #eee ;", "background-color : #f4f1ec ;"),
    ("background-color : #eee;", "background-color : #f4f1ec ;"),

    # Текст — чёрный → тёмно-зелёный (только для ссылок/заголовков)
    ("color : #000000 ;", "color : #1a5c33 ;"),
    ("color : #000000;", "color : #1a5c33 ;"),
    ("color : #000 ;", "color : #1a5c33 ;"),
    ("color : #000;", "color : #1a5c33 ;"),

    # Серый текст
    ("color : #606060 ;", "color : #5a554e ;"),
    ("color : #606060;", "color : #5a554e ;"),
    ("color : #666 ;", "color : #5a554e ;"),
    ("color : #666;", "color : #5a554e ;"),
    ("color : #888 ;", "color : #8a8378 ;"),
    ("color : #888;", "color : #8a8378 ;"),

    # Белый текст на тёмном — оставить белым
    # (ничего не делаем)
]

def update_css_file(filepath):
    """Обновить один CSS-файл."""
    try:
        with open(filepath, 'r', encoding='cp1251', errors='replace') as f:
            content = f.read()
    except Exception as e:
        print(f"  SKIP {filepath}: {e}")
        return 0

    original = content
    changes = 0

    for old, new in COLOR_MAP:
        if old in content:
            count = content.count(old)
            content = content.replace(old, new)
            changes += count

    if content != original:
        try:
            with open(filepath, 'w', encoding='cp1251', errors='replace') as f:
                f.write(content)
            return changes
        except Exception as e:
            print(f"  ERROR writing {filepath}: {e}")
            return 0
    return 0

if __name__ == '__main__':
    root_dir = sys.argv[1] if len(sys.argv) > 1 else '/e/VLSE/labalse_front'

    total_files = 0
    total_changes = 0

    for dirpath, dirnames, filenames in os.walk(root_dir):
        # Пропускаем .git и ext-lib
        if '.git' in dirpath or 'ext-lib' in dirpath:
            continue

        for fname in filenames:
            if fname.endswith('.css') and not fname.endswith('.css.bak'):
                filepath = os.path.join(dirpath, fname)
                changes = update_css_file(filepath)
                if changes > 0:
                    print(f"  + {filepath.replace(root_dir, '')}: {changes} changes")
                    total_files += 1
                    total_changes += changes

    print(f"\nDone: {total_files} files updated, {total_changes} total replacements")
