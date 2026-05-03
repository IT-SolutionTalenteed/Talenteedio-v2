<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'string'   => 'Le champ :attribute doit être une chaîne de caractères.',
    'max'      => [
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
    ],
    'unique'   => 'Cette valeur est déjà utilisée.',
    'email'    => 'Le champ :attribute doit être une adresse email valide.',
    'numeric'  => 'Le champ :attribute doit être un nombre.',
    'integer'  => 'Le champ :attribute doit être un entier.',
    'min'      => [
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'in'       => 'La valeur sélectionnée pour :attribute est invalide.',
    'boolean'  => 'Le champ :attribute doit être vrai ou faux.',
    'nullable' => '',
    'mimes'    => 'Le champ :attribute doit être un fichier de type : :values.',
    'image'    => 'Le champ :attribute doit être une image.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
];
