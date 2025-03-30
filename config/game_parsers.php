<?php

return [
    'site1' => [
        'url' => 'http://site1.com',
        'selectors' => [
            'game' => '.game-item', // Селектор для каждой игры
            'title' => '.game-title', // Селектор для названия игры
            'description' => '.game-description', // Селектор для описания игры
        ],
        'pagination' => [
            'type' => 'query_param', // Тип пагинации (query_param, next_button)
            'param' => 'page', // Параметр пагинации (если тип query_param)
            'next_button' => '.next-page', // Селектор кнопки "Следующая страница" (если тип next_button)
        ],
    ]
];