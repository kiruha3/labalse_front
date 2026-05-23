#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Радикальный редизайн base.css + index.css.
Швейцарский минимализм: крупная типографика, воздух, тёмный сайдбар.
БЕЗ скруглений. Солидный гос. вид.
"""

import sys

def update_base_css(filepath):
    with open(filepath, 'r', encoding='cp1251', errors='replace') as f:
        content = f.read()

    # 1. Шапка — выше, более монументальная
    content = content.replace(
        '#page-head {\n\t\tposition : relative ;\n\t\tpadding : 4px ;\n\t\tbackground : linear-gradient(180deg, #123d20 0%, #1a5c33 100%) ;\n\t}',
        '#page-head {\n\t\tposition : relative ;\n\t\tpadding : 16px 4px 12px 4px ;\n\t\tbackground : linear-gradient(180deg, #0f2e1a 0%, #1a5c33 100%) ;\n\t}'
    )

    # 2. Заголовок организации — ещё крупнее
    content = content.replace(
        '.mhTitle1 {\n\t\tfont-size : 16pt ;\n\t\tcolor : #ffffff ;\n\t}',
        '.mhTitle1 {\n\t\tfont-size : 20pt ;\n\t\tfont-weight : 600 ;\n\t\tcolor : #ffffff ;\n\t\tletter-spacing : 1px ;\n\t}'
    )
    content = content.replace(
        '.mhTitle2 {\n\t\tfont-size : 13pt ;\n\t\tcolor : #e8e4de ;\n\t}',
        '.mhTitle2 {\n\t\tfont-size : 14pt ;\n\t\tcolor : #a8c4b0 ;\n\t\tfont-weight : 300 ;\n\t}'
    )

    # 3. Меню — крупнее, с отступами, без hover красного
    content = content.replace(
        '.mhMenu TD { font-size : 9pt ; font-weight : 500 ; text-align : center ; }',
        '.mhMenu TD { font-size : 11pt ; font-weight : 500 ; text-align : center ; padding : 10px 8px ; }'
    )
    content = content.replace(
        '.mhMenu a { text-decoration : none ; color : #ffffff ; }',
        '.mhMenu a { text-decoration : none ; color : #d4e8db ; padding : 6px 12px ; display : inline-block ; border-bottom : 2px solid transparent ; transition : all 0.2s ; }'
    )
    content = content.replace(
        '.mhMenu a:hover { color : #8b2e2e ; }',
        '.mhMenu a:hover { color : #ffffff ; border-bottom : 2px solid #49a67b ; }'
    )

    # 4. Базовый шрифт крупнее
    content = content.replace(
        'font-family : "Segoe UI", "Helvetica Neue", Arial, sans-serif ;',
        'font-family : "Segoe UI", "Helvetica Neue", Arial, sans-serif ;\n\t\tfont-size : 14px ;'
    )

    # 5. Таблицы — чище, без вертикальных границ
    # blt таблица — без внешней рамки, только горизонтальные линии
    content = content.replace(
        '\t.blt {\n\t\tborder-collapse : separate ;\n\t\tborder-spacing : 0 ;\n\t\tborder : none !important ;\n\t\tmin-width : auto !important ;\n\t\twidth : 100% !important ;\n\t\tmax-width : 100% !important ;\n\t\tbackground : var(--color-bg-white) ;\n\t\tborder-radius : var(--radius-md) ;\n\t\tbox-shadow : var(--shadow-sm) ;\n\t\toverflow : hidden ;\n\t\tmargin-bottom : var(--space-md) ;\n\t\tfont-size : var(--font-size-base) ;\n\t}',
        '\t.blt {\n\t\tborder-collapse : collapse ;\n\t\twidth : 100% !important ;\n\t\tfont-size : 14px ;\n\t\tborder-top : 2px solid #1a5c33 ;\n\t\tborder-bottom : 2px solid #1a5c33 ;\n\t}'
    )

    # Заголовки таблиц — более контрастные
    content = content.replace(
        '\t.blch-0, .blch-1, .blch-2, .blch-3, .blch-4, .blch-5 {\n\t\tfont-size : var(--font-size-base) !important ;\n\t\tfont-weight : 600 !important ;\n\t\ttext-align : center ;\n\t\tbackground : linear-gradient(180deg, var(--color-primary) 0%, var(--color-primary-dark) 100%) !important ;\n\t\tcolor : var(--color-white) !important ;\n\t\tpadding : 12px var(--space-sm) !important ;\n\t\tborder : none !important ;\n\t\tborder-bottom : 2px solid var(--color-primary-light) !important ;\n\t\ttext-transform : uppercase ;\n\t\tletter-spacing : 0.3px ;\n\t\tfont-size : 11px !important ;\n\t}',
        '\t.blch-0, .blch-1, .blch-2, .blch-3, .blch-4, .blch-5 {\n\t\tfont-size : 11px !important ;\n\t\tfont-weight : 600 !important ;\n\t\ttext-align : center ;\n\t\tbackground-color : #1a5c33 !important ;\n\t\tcolor : #ffffff !important ;\n\t\tpadding : 12px 8px !important ;\n\t\tborder : none !important ;\n\t\tborder-bottom : 3px solid #0f2e1a !important ;\n\t\ttext-transform : uppercase ;\n\t\tletter-spacing : 0.5px ;\n\t}'
    )

    # Ячейки — больше padding, чище
    content = content.replace(
        '\t.blc-0-1, .blc-0-2, .blc-0-3, .blc-0-4,\n\t.blc-1, .blc-2, .blc-3, .blc-4, .blc-5 {\n\t\tfont-size : var(--font-size-base) !important ;\n\t\tborder : none !important ;\n\t\tborder-bottom : 1px solid var(--color-border-light) !important ;\n\t\tpadding : 10px var(--space-sm) !important ;\n\t\tcolor : var(--color-text) ;\n\t}',
        '\t.blc-0-1, .blc-0-2, .blc-0-3, .blc-0-4,\n\t.blc-1, .blc-2, .blc-3, .blc-4, .blc-5 {\n\t\tfont-size : 14px !important ;\n\t\tborder : none !important ;\n\t\tborder-bottom : 1px solid #e8e4de !important ;\n\t\tpadding : 12px 10px !important ;\n\t\tcolor : #222222 ;\n\t}'
    )

    # Строки — более заметное чередование
    content = content.replace(
        '\t.blr-0 {\n\t\tbackground-color : var(--color-bg-white) !important ;\n\t\ttransition : background 0.15s ease ;\n\t}',
        '\t.blr-0 {\n\t\tbackground-color : #ffffff !important ;\n\t}'
    )
    content = content.replace(
        '\t.blr-1 {\n\t\tbackground-color : var(--color-bg-light) !important ;\n\t\ttransition : background 0.15s ease ;\n\t}',
        '\t.blr-1 {\n\t\tbackground-color : #f8f6f3 !important ;\n\t}'
    )
    content = content.replace(
        '\t.blr-0:hover,\n\t.blr-1:hover {\n\t\tbackground-color : #e8f5ec !important ;\n\t}',
        '\t.blr-0:hover,\n\t.blr-1:hover {\n\t\tbackground-color : #edf5f0 !important ;\n\t}'
    )

    # Кнопки внутри таблиц — крупнее
    content = content.replace(
        '\t.cmon_link a {\n\t\tpadding : 8px 16px !important ;\n\t\tborder : none !important ;\n\t\ttext-decoration : none !important ;\n\t\tbackground : var(--color-bg-light) !important ;\n\t\tfont-size : var(--font-size-base) !important ;\n\t\tcolor : var(--color-text) !important ;\n\t\tborder-radius : var(--radius-sm) ;\n\t\tdisplay : inline-block ;\n\t\tmargin : 2px ;\n\t\ttransition : all 0.2s ease ;\n\t\tbox-shadow : var(--shadow-sm) ;\n\t}',
        '\t.cmon_link a {\n\t\tpadding : 8px 16px !important ;\n\t\tborder : 1px solid #c9c3bb !important ;\n\t\ttext-decoration : none !important ;\n\t\tbackground : #f4f1ec !important ;\n\t\tfont-size : 13px !important ;\n\t\tcolor : #1a5c33 !important ;\n\t\tdisplay : inline-block ;\n\t\tmargin : 2px ;\n\t\ttransition : all 0.15s ;\n\t}'
    )
    content = content.replace(
        '\t.cmon_link a:hover {\n\t\tbackground : var(--color-primary) !important ;\n\t\tcolor : var(--color-white) !important ;\n\t\ttransform : translateY(-1px) ;\n\t\tbox-shadow : var(--shadow-md) ;\n\t}',
        '\t.cmon_link a:hover {\n\t\tbackground : #1a5c33 !important ;\n\t\tcolor : #ffffff !important ;\n\t\tborder-color : #1a5c33 !important ;\n\t}'
    )

    # mon_link — основные кнопки
    content = content.replace(
        '\t.mon_link a {\n\t\tpadding : 8px 16px !important ;\n\t\tborder : none !important ;\n\t\ttext-decoration : none !important ;\n\t\tbackground : linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%) !important ;\n\t\tfont-size : var(--font-size-sm) !important ;\n\t\tcolor : var(--color-white) !important ;\n\t\tborder-radius : var(--radius-sm) ;\n\t\tdisplay : inline-block ;\n\t\tmargin : 2px ;\n\t\ttransition : all 0.2s ease ;\n\t\tbox-shadow : var(--shadow-sm) ;\n\t\ttext-transform : uppercase ;\n\t\tletter-spacing : 0.5px ;\n\t\tfont-weight : 600 ;\n\t}',
        '\t.mon_link a {\n\t\tpadding : 10px 20px !important ;\n\t\tborder : none !important ;\n\t\ttext-decoration : none !important ;\n\t\tbackground : #1a5c33 !important ;\n\t\tfont-size : 12px !important ;\n\t\tcolor : #ffffff !important ;\n\t\tdisplay : inline-block ;\n\t\tmargin : 3px ;\n\t\ttransition : all 0.15s ;\n\t\ttext-transform : uppercase ;\n\t\tletter-spacing : 0.8px ;\n\t\tfont-weight : 600 ;\n\t}'
    )
    content = content.replace(
        '\t.mon_link a:hover {\n\t\tbackground : linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 100%) !important ;\n\t\ttransform : translateY(-1px) ;\n\t\tbox-shadow : var(--shadow-md) ;\n\t}',
        '\t.mon_link a:hover {\n\t\tbackground : #123d20 !important ;\n\t}'
    )

    with open(filepath, 'w', encoding='cp1251', errors='replace') as f:
        f.write(content)
    print(f"Radical update: {filepath}")


def update_index_css(filepath):
    with open(filepath, 'r', encoding='cp1251', errors='replace') as f:
        content = f.read()

    # 1. Левый сайдбар — тёмный, шире
    content = content.replace(
        '\t#left-column {\n\t\twidth: 256px;\n\t\tpadding-left: 0;\n\t\tpadding-top: 0;\n\t\tpadding-right: 4px;\n\t\tpadding-bottom: 0;\n\t\tvertical-align: top;\n\t}',
        '\t#left-column {\n\t\twidth: 280px;\n\t\tpadding: 16px;\n\t\tvertical-align: top;\n\t\tbackground: #123d20;\n\t}'
    )

    # 2. Секции в сайдбаре — без внешних границ
    content = content.replace(
        '\t#sections {\n\t\tmargin: 0;\n\t\twidth: 100%;\n\t\tborder-collapse: collapse;\n\t}',
        '\t#sections {\n\t\tmargin: 0 0 20px 0;\n\t\twidth: 100%;\n\t\tborder-collapse: collapse;\n\t}'
    )

    # 3. Названия разделов — крупные, белые
    content = content.replace(
        '\t.section-name a {\n\t\tfont-weight: bold;\n\t\tfont-size: 8pt;\n\t\tcolor: #000000;\n\t\ttext-decoration: none;\n\t}',
        '\t.section-name a {\n\t\tfont-weight: 600;\n\t\tfont-size: 13px;\n\t\tcolor: #ffffff;\n\t\ttext-decoration: none;\n\t\tdisplay: block;\n\t\tpadding: 8px 0;\n\t\tborder-bottom: 1px solid #1a5c33;\n\t}'
    )
    content = content.replace(
        '\t.section-name a:hover {\n\t\tcolor: #ff0000;\n\t}',
        '\t.section-name a:hover {\n\t\tcolor: #a8c4b0;\n\t}'
    )

    # 4. Описания разделов — светло-зелёные
    content = content.replace(
        '\t.section-desc {\n\t\ttext-align: justify;\n\t\tvertical-align: text-top;\n\t\tpadding-left: 8px;\n\t\tpadding-bottom: 16px;\n\t\tfont-size: 8pt;\n\t\tcolor: #606060;\n\t}',
        '\t.section-desc {\n\t\ttext-align: left;\n\t\tvertical-align: text-top;\n\t\tpadding: 4px 0 16px 4px;\n\t\tfont-size: 11px;\n\t\tcolor: #a8c4b0;\n\t\tline-height: 1.4;\n\t}'
    )

    # 5. Заголовки панелей — без » и cap2.gif, чистые
    content = content.replace(
        '\t.panel-header {\n\t\tborder: 1px solid #ccc ;\n\t\theight: 19px;\n\t\tbackground: #eee url(cap2.gif);\n\t\tpadding: 0;\n\t\ttext-indent: 8px;\n\t\ttext-align: left;\n\t\tfont-size: 10pt;\n\t\tcolor: #000;\n\t}',
        '\t.panel-header {\n\t\tbackground: #1a5c33;\n\t\tpadding: 10px 14px;\n\t\ttext-align: left;\n\t\tfont-size: 12px;\n\t\tfont-weight: 600;\n\t\tcolor: #ffffff;\n\t\ttext-transform: uppercase;\n\t\tletter-spacing: 0.8px;\n\t\tborder-bottom: 3px solid #0f2e1a;\n\t}'
    )

    # 6. Контент панелей — чистый белый, без рамок
    content = content.replace(
        '\t.panel-content {\n\t\tborder: 1px solid #c0c0c0;\n\t\tpadding: 8px;\n\t\tbackground-color: #f0f0f0;\n\t}',
        '\t.panel-content {\n\t\tpadding: 16px;\n\t\tbackground-color: #ffffff;\n\t}'
    )
    content = content.replace(
        '\t.panel-content-2 {\n\t\tborder: 1px solid #c0c0c0;\n\t\tpadding: 8px;\n\t\tbackground-color: #f0f0f0;\n\t\ttext-align : center ;\n\t}',
        '\t.panel-content-2 {\n\t\tpadding: 16px;\n\t\tbackground-color: #ffffff;\n\t\ttext-align : center ;\n\t}'
    )

    # 7. Tools — ссылки в сайдбаре
    content = content.replace(
        '\t#tools {\n\t\tmargin: 0;\n\t\twidth: 100%;',
        '\t#tools {\n\t\tmargin: 0 0 20px 0;\n\t\twidth: 100%;'
    )

    # 8. Searchers
    content = content.replace(
        '\t#searchers {\n\t\tmargin: 0;\n\t\twidth: 100%;',
        '\t#searchers {\n\t\tmargin: 0 0 20px 0;\n\t\twidth: 100%;'
    )

    # 9. Отступ между панелями
    content = content.replace(
        '\t.panel-spacer {\n\t\theight : 0.5cm ;\n\t}',
        '\t.panel-spacer {\n\t\theight : 20px ;\n\t}'
    )

    # 10. Центральная и правая колонки — padding
    content = content.replace(
        '\t#middle-column {\n\t\tvertical-align: top;',
        '\t#middle-column {\n\t\tvertical-align: top;\n\t\tpadding: 0 20px;'
    )
    content = content.replace(
        '\t#right-column {\n\t\twidth: 220px;\n\t\tvertical-align: top;',
        '\t#right-column {\n\t\twidth: 240px;\n\t\tvertical-align: top;\n\t\tpadding: 0 16px 0 0;'
    )

    with open(filepath, 'w', encoding='cp1251', errors='replace') as f:
        f.write(content)
    print(f"Radical update: {filepath}")


if __name__ == '__main__':
    update_base_css('base.css')
    update_index_css('index.css')
    print("Done!")
