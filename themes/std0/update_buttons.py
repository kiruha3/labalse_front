#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Обновление buttons.css — зелёные кнопки вместо серых."""

import sys

def update_file(filepath):
    with open(filepath, 'r', encoding='cp1251', errors='replace') as f:
        content = f.read()

    original = content

    # Заменяем серый градиент на зелёный
    content = content.replace(
        'background: #f0f0f0; /* Old browsers */',
        'background: #1a5c33; /* Old browsers */'
    )
    content = content.replace(
        'background: -moz-linear-gradient(top,  #f0f0f0 0%, #c0c0c0 100%); /* FF3.6+ */',
        'background: -moz-linear-gradient(top,  #217a3d 0%, #1a5c33 100%); /* FF3.6+ */'
    )
    content = content.replace(
        'background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f0f0f0), color-stop(100%,#c0c0c0)); /* Chrome,Safari4+ */',
        'background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#217a3d), color-stop(100%,#1a5c33)); /* Chrome,Safari4+ */'
    )
    content = content.replace(
        'background: -webkit-linear-gradient(top,  #f0f0f0 0%,#c0c0c0 100%); /* Chrome10+,Safari5.1+ */',
        'background: -webkit-linear-gradient(top,  #217a3d 0%,#1a5c33 100%); /* Chrome10+,Safari5.1+ */'
    )
    content = content.replace(
        'background: -o-linear-gradient(top,  #f0f0f0 0%,#c0c0c0 100%); /* Opera 11.10+ */',
        'background: -o-linear-gradient(top,  #217a3d 0%,#1a5c33 100%); /* Opera 11.10+ */'
    )
    content = content.replace(
        'background: -ms-linear-gradient(top,  #f0f0f0 0%,#c0c0c0 100%); /* IE10+ */',
        'background: -ms-linear-gradient(top,  #217a3d 0%,#1a5c33 100%); /* IE10+ */'
    )
    content = content.replace(
        'background: linear-gradient(to bottom,  #f0f0f0 0%,#c0c0c0 100%); /* W3C */',
        'background: linear-gradient(to bottom,  #217a3d 0%,#1a5c33 100%); /* W3C */'
    )
    content = content.replace(
        "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f0f0f0', endColorstr='#c0c0c0',GradientType=0 ); /* IE6-9 */",
        "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#217a3d', endColorstr='#1a5c33',GradientType=0 ); /* IE6-9 */"
    )

    # Цвет текста на кнопках — белый
    content = content.replace('color: #000 ;', 'color: #ffffff ;')

    # Disabled — тёплый серый
    content = content.replace(
        'background: #ccc ;',
        'background: #a09888 ;'
    )
    content = content.replace(
        'color: #666 ;',
        'color: #e8e4de ;'
    )

    # Hover — более тёмный зелёный
    content = content.replace(
        'box-shadow : inset 0 0 0.5em #08f ;',
        'box-shadow : inset 0 0 0.5em #0d3d1f ;'
    )
    content = content.replace(
        'color : #08f ;',
        'color : #d4e8db ;'
    )

    # Граница кнопок
    content = content.replace('border : 1px solid #888 ;', 'border : 1px solid #1a5c33 ;')
    content = content.replace('border : 1px solid #666 ;', 'border : 1px solid #666 ;')  # keep disabled

    with open(filepath, 'w', encoding='cp1251', errors='replace') as f:
        f.write(content)

    changes = sum(1 for s in [original] if content != original)
    return 1 if content != original else 0

if __name__ == '__main__':
    filepath = sys.argv[1] if len(sys.argv) > 1 else 'buttons.css'
    changed = update_file(filepath)
    print(f"Updated {filepath}: {'changes applied' if changed else 'no changes'}")
