<?php

return [
    // Countries list
    'countries' => ["United States", "Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling Islands)", "Colombia", "Comoros", "Congo", "Cook Islands", "Costa Rica", "Cote D'Ivoire (Ivory Coast)", "Croatia (Hrvatska", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France", "Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and McDonald Islands", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea (North)", "Korea (South)", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and The Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovak Republic", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "S. Georgia and S. Sandwich Isls.", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "US Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City State (Holy See)", "Venezuela", "Viet Nam", "Virgin Islands (British)", "Virgin Islands (US)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zaire", "Zambia", "Zimbabwe"],
    // State list
    'states' => ["AK", "AL", "AR", "AS", "AZ", "CA", "CO", "CT", "DC", "DE", "FL", "GA", "GU", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME", "MI", "MN", "MO", "MP", "MS", "MT", "NC", "ND", "NE", "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "PR", "RI", "SC", "SD", "TN", "TX", "UM", "UT", "VA", "VI", "VT", "WA", "WI", "WV", "WY"],

    // Class Types
    'class_types' => [
        [ 'id' => 1, 'name' => 'K-3 Grades (No Student login)'],
        [ 'id' => 2, 'name' => '4-5 Grades'],
        [ 'id' => 3, 'name' => '6-8 Grades'],
        [ 'id' => 4, 'name' => '9-12+ Grades']
    ],

    // Courseware Types
    'courseware_types' => [
        1 => 'Elementary Courseware',
        2 => 'Elementary Courseware',
        3 => 'Middle School Courseware',
        4 => 'High School Courseware'
    ],

    // Activation roles
    'activation_roles' => [ // Permissions == roles who can add this role
        [ 'id' => 1, 'role' => 4, 'slug' => 'student', 'name' => 'Student', 'permissions' => [3,3,7,6]],
        [ 'id' => 2, 'role' => 7, 'slug' => 'assistant-teacher', 'name' => 'Assistant', 'permissions' => [3, 6]],
        [ 'id' => 3, 'role' => 3, 'slug' => 'teacher', 'name' => 'Teacher', 'permissions' => [6]],
        [ 'id' => 4, 'role' => 3, 'slug' => 'hybrid', 'name' => 'Teacher / School Admin', 'permissions' => [6]],
        [ 'id' => 5, 'role' => 6, 'slug' => 'school-admin', 'name' => 'School Admin', 'permissions' => [6]]
    ],

    // Additional pages to insert into 'insert pages' select under manage assignments
    'insert_page_list' => [
        (object) [
            'id' => 'dashboard',
            'link' => '/dashboard',
            'title' => 'Dashboard (Sidebar)',
            'class_types' => [1, 2, 3, 4]
        ]
    ],

    // Available Video Conference Services
    'videocon_services' => [
        ['id' => 'zoom', 'name' => 'Zoom', 'setting' => 'videocon_zoom'],
        ['id' => 'google', 'name' => 'Google Meet', 'setting' => 'videocon_google']
    ],

    // Global password requirements
    'password_regex' => '/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d\W_]{6,}$/',

    // Courseware main menu items
    'courseware_menu' => [
        'highschool' => [
            ['url' => '/course/hs', 'name' => 'Get Started'],
            ['url' => '/course/hs/introduction', 'name' => 'Introduction'],
            ['url' => '/course/hs/9-step-method', 'name' => '9 Step Method'],
            ['url' => '/course/hs/abcs-of-storytelling-beta', 'name' => 'ABCs of Storytelling'],
            ['url' => '/course/hs/321-production', 'name' => '3, 2, 1 Production!'],
            ['url' => '/course/hs/3d-printing', 'name' => '123s of 3D Printing'],
        ],
        'elementary' => [
            ['url' => '/course/em', 'name' => 'Get Started'],
            ['url' => '/course/em/history', 'name' => 'History'],
            ['url' => '/course/em/9-step-method', 'name' => '9 Step Method'],
            ['url' => '/course/em/abcs-of-storytelling', 'name' => 'ABCs of Storytelling'],
            ['url' => '/course/em/3d-printing', 'name' => '123s of 3D Printing'],
        ]
    ]

];
