<?php

return [

    'before'               => 'Le champ :attribute doit être une date avant :date.',
    'between'              => [
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
    ],
    'date'                 => 'Cette date n’est pas valide.',

    'custom' => [
        'dt' => [
            'regex' => 'La date doit être indiquée au format JJ.MM.AAAA.',
        ],
        'p' => [
            'required' => 'Un numéro de page est requis en l’absence de date précise.'
        ]
    ],

    'attributes' => [
        'dt' => 'date',
        'n' => 'cahier',
        'p' => 'page'
    ],

];
