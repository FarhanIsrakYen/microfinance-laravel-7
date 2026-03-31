<?php

return [

    'mfn_savings_accounts' => [
        'insert' => [
            'subject' => 'Account opening',
            'body'    => 'Your account is successfully opened at :date.',
        ],
        'update' => [
            'subject' => 'Account opening update',
            'body'    => '[Update] Your account is successfully opened at :date.',
        ],
    ],
    'mfn_savings_closings' => [
        'insert' => [
            'subject' => 'Account closed',
            'body'    => 'Your account is successfully closed.',
        ],
        'update' => [
            'subject' => 'Account closed update',
            'body'    => '[Update] Your account is successfully closed.',
        ],
    ],
    'mfn_savings_deposit'  => [
        'insert' => [
            'subject' => 'Deposit on your account',
            'body'    => 'Your account is credited TK :amount at :date',
        ],
        'update' => [
            'subject' => 'Deposit on your account update',
            'body'    => '[Update] Your account is credited TK :amount at :date',
        ],
    ],
    'mfn_savings_withdraw' => [
        'insert' => [
            'subject' => 'Withdarw from your account',
            'body'    => 'Your account is debited TK :amount at :date',
        ],
        'update' => [
            'subject' => 'Withdarw from your account update',
            'body'    => '[Update] Your account is debited TK :amount at :date',
        ],
    ],
    'mfn_members'  => [
        'insert' => [
            'subject' => 'Admission member',
            'body'    => "You'er admited.",
        ],
        'update' => [
            'subject' => 'Admission member Update',
            'body'    => "[Update] You'er admited.",
        ]
    ],
    'mfn_loans' => [
        'insert' => [
            'subject' => 'Loan admission',
            'body'    => "You're take loan TK :amount at :date",
        ],
        'update' => [
            'subject' => 'Loan admission update',
            'body'    => "[Update] You're take loan TK :amount at :date",
        ],
    ],
    'mfn_member_closings' => [
        'insert' => [
            'subject' => 'Member Closing',
            'body'    => "Your account is closed!!",
        ],
        'update' => [
            'subject' => 'Member Closing Update',
            'body'    => "[Update] Your account is closed!!",
        ],
    ],
    'mfn_member_primary_product_transfers' => [
        'insert' => [
            'subject' => 'Member Product Transfer',
            'body'    => "Your account is successfully transfered!!",
        ],
        'update' => [
            'subject' => 'Member Product Transfer Update',
            'body'    => "[Update] Your account is successfully transfered!!",
        ],
    ]
];
