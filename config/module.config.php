<?php
return [
    // Config of nightclub_id in route exist
    'service_manager' => [
        'factories' => [
            'Strapieno\Utils\Listener\ListenerManager' => 'Strapieno\Utils\Listener\ListenerManagerFactory'
        ],
        'invokables' => [
            'Strapieno\Utils\Delegator\AttachRestResourceListenerDelegator' => 'Strapieno\Utils\Delegator\AttachRestResourceListenerDelegator'
        ],
        'aliases' => [
            'listenerManager' => 'Strapieno\Utils\Listener\ListenerManager'
        ]
    ],
    // Register listener to listener manager
    'service-listeners' => [
        'initializers' => [
            'Strapieno\NightClub\Model\NightClubModelInitializer'
        ],
        'invokables' => [
            'Strapieno\PlaceGallery\Api\Listener\NightClubRestListener'
                => 'Strapieno\PlaceGallery\Api\Listener\NightClubRestListener'
        ]
    ],
    'attach-resource-listeners' => [
        'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
            'Strapieno\PlaceGallery\Api\Listener\NightClubRestListener'
        ]
    ],
    'controllers' => [
        'delegators' => [
            'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
                'Strapieno\Utils\Delegator\AttachRestResourceListenerDelegator',
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'api-rest' => [
                'child_routes' => [
                    'place' => [
                        'child_routes' => [
                            'gallery' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/gallery',
                                    'defaults' => [
                                        'controller' => 'Strapieno\PlaceGallery\Api\V1\Rest\Controller'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'imgman-apigility' => [
        'imgman-connected' => [
            'Strapieno\PlaceGallery\Api\V1\Rest\ConnectedResource' => [
                'service' => 'ImgMan\Service\PlaceGallery'
            ],
        ],
    ],
    'zf-rest' => [
        'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
            'service_name' => 'place-gallery',
            'listener' => 'Strapieno\PlaceGallery\Api\V1\Rest\ConnectedResource',
            'route_name' => 'api-rest/place/gallery',
            'route_identifier_name' => 'nightclub_id',
            'entity_http_methods' => [
                0 => 'GET',
                2 => 'PUT',
                3 => 'DELETE'
            ],
            'page_size' => 10,
            'page_size_param' => 'page_size',
            'collection_class' => 'Zend\Paginator\Paginator',
            'entity_class' => 'Strapieno\PlaceGallery\Model\Entity\dEntity'
        ]
    ],
    'zf-content-negotiation' => [
        'accept_whitelist' => [
            'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
                'application/hal+json',
                'application/json'
            ],
        ],
        'content_type_whitelist' => [
            'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
                'application/json',
                'multipart/form-data',
            ],
        ],
    ],
    'zf-hal' => [
        // map each class (by name) to their metadata mappings
        'metadata_map' => [
            'Strapieno\PlaceGallery\Model\Entity\PlaceGallery' => [
                'entity_identifier_name' => 'id',
                'route_name' => 'api-rest/place/gallery',
                'route_identifier_name' => 'nightclub_id',
                'hydrator' => 'Strapieno\Utils\Hydrator\ImageBase64Hydrator',
            ],
        ],
    ],
    'zf-content-validation' => [
        'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
            'input_filter' => 'PlaceGalleryInputFilter',
        ],
    ],
    'strapieno_input_filter_specs' => [
        'PlaceGalleryInputFilter' => [
            [
                'name' => 'blob',
                'required' => true,
                'allow_empty' => false,
                'continue_if_empty' => false,
                'validators' => [
                    0 => [
                        'name' => 'fileuploadfile',
                        'break_chain_on_failure' => true,
                    ],
                    1 => [
                        'name' => 'filesize',
                        'break_chain_on_failure' => true,
                        'options' => [
                            'min' => '20KB',
                            'max' => '8MB',
                        ],
                    ],
                    2 => [
                        'name' => 'filemimetype',
                        'options' => [
                            'mimeType' => [
                                'image/png',
                                'image/jpeg',
                            ],
                            'magicFile' => false,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
