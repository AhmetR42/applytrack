<?php

// Dutch messages for the validation rules this app uses.
// Anything missing here falls back to Laravel's English messages.

return [
    'before_or_equal' => ':Attribute moet een datum zijn vóór of gelijk aan :date.',
    'confirmed' => 'De bevestiging van :attribute komt niet overeen.',
    'date' => ':Attribute moet een geldige datum zijn.',
    'email' => ':Attribute moet een geldig e-mailadres zijn.',
    'enum' => 'De gekozen :attribute is ongeldig.',
    'in' => 'De gekozen :attribute is ongeldig.',
    'lowercase' => ':Attribute mag alleen kleine letters bevatten.',
    'max' => [
        'string' => ':Attribute mag niet meer dan :max tekens bevatten.',
    ],
    'min' => [
        'string' => ':Attribute moet minimaal :min tekens bevatten.',
    ],
    'required' => ':Attribute is verplicht.',
    'string' => ':Attribute moet tekst zijn.',
    'unique' => 'Dit :attribute is al in gebruik.',
    'url' => ':Attribute moet een geldige URL zijn.',

    'attributes' => [
        'applied_on' => 'sollicitatiedatum',
        'company' => 'bedrijf',
        'contact_email' => 'contact e-mail',
        'email' => 'e-mailadres',
        'interview_at' => 'gespreksdatum',
        'location' => 'locatie',
        'name' => 'naam',
        'notes' => 'notities',
        'password' => 'wachtwoord',
        'position' => 'functie',
        'status' => 'status',
        'url' => 'vacaturelink',
    ],
];
