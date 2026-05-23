#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Обновление index.css — главная страница."""

import sys

def update_file(filepath):
    with open(filepath, 'r', encoding='cp1251', errors='replace') as f:
        content = f.read()

    # Панели — зелёный заголовок, белый контент
    content = content.replace(
        'background: #eee url(cap2.gif);',
        'background: #1a5c33;'
    )
    content = content.replace(
        'color: #000;',
        'color: #ffffff;'
    )
    content = content.replace(
        'background-color: #f0f0f0;',
        'background-color: #ffffff;'
    )

    # Секции
    content = content.replace(
        'color: #000000;',
        'color: #1a5c33;'
    )
    content = content.replace(
        'color: #ff0000;',
        'color: #b85c3e;'
    )
    content = content.replace(
        'color: #606060;',
        'color: #5a554e;'
    )

    # Границы панелей
    content = content.replace(
        'border: 1px solid #ccc ;',
        'border: 1px solid #c9c3bb ;'
    )
    content = content.replace(
        'border: 1px solid #c0c0c0;',
        'border: 1px solid #c9c3bb ;'
    )

    with open(filepath, 'w', encoding='cp1251', errors='replace') as f:
        f.write(content)

if __name__ == '__main__':
    filepath = sys.argv[1] if len(sys.argv) > 1 else 'index.css'
    update_file(filepath)
    print(f"Updated {filepath}")
