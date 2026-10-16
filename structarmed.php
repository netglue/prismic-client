<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY(), Preset::YAGNI())
    ->layer('Exception', 'src/Exception')
    ->layer('Json', 'src/Json.php')
    ->layer('Value', 'src/Value')
    ->layer('Link', [
        'src/Link.php',
        'src/LinkVariant.php',
        'src/UrlLink.php',
        'src/LinkResolver.php',
        'src/DefaultLinkResolver.php',
    ])
    ->layer('Fragment', [
        'src/Document/Fragment',
        'src/Document/Fragment.php',
        'src/Document/FragmentCollection.php',
    ])
    ->layer(
        'Document',
        [
            'src/Document.php',
            'src/Document',
        ],
        // fragments live under src/Document but are part of the Fragment layer
        [
            'src/Document/Fragment',
            'src/Document/Fragment.php',
            'src/Document/FragmentCollection.php',
        ]
    )
    ->layer('ResultSet', [
        'src/ResultSet',
        'src/ResultSet.php',
    ])
    ->layer('Serializer', 'src/Serializer')
    ->layer(
        'Client',
        // all classes in src/ marked as Client
        'src',
        // except these directories/files as exclusions
        // and they are part of specific layers defined above
        [
            'src/Exception',
            'src/Json.php',
            'src/Value',
            'src/Link.php',
            'src/LinkVariant.php',
            'src/UrlLink.php',
            'src/LinkResolver.php',
            'src/DefaultLinkResolver.php',
            'src/Document',
            'src/Document.php',
            'src/ResultSet',
            'src/ResultSet.php',
            'src/Serializer',
        ]
    )
    ->ruleset([
        'Json'       => ['Exception'],
        'Exception'  => ['Json', 'Value', 'Fragment'],
        'Link'       => ['Fragment'],
        'Document'   => ['Fragment', 'Value'],
        'Value'      => ['+Document', 'Exception'],
        'Fragment'   => ['+Document', 'Exception', 'Link'],
        'ResultSet'  => ['+Document', 'Exception', 'Json'],
        'Serializer' => ['+Link', 'Exception'],
        'Client'     => ['+ResultSet'],
    ]);
