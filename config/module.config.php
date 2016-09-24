<?php
return [
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
    'controllers' => [
        'delegators' => [
            'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
                'Strapieno\Utils\Delegator\AttachRestResourceListenerDelegator',
            ]
        ],
    ],
    'service-listeners' => [
        'initializers' => [
            'Strapieno\Place\Model\PlaceModelInitializer'
        ],
        'invokables' => [
            'Strapieno\PlaceGallery\Api\Listener\PlaceGalleryRestListener' => 'Strapieno\PlaceGallery\Api\Listener\PlaceGalleryRestListener',
        ]
    ],
    'attach-resource-listeners' => [
        'Strapieno\PlaceGallery\Api\V1\Rest\Controller' => [
            'Strapieno\PlaceGallery\Api\Listener\PlaceGalleryRestListener'
        ]
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
                                    'route' => '/gallery[/:gallery_id]',
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
            'route_identifier_name' => 'gallery_id',
            'entity_http_methods' => [
                0 => 'GET',
                2 => 'PUT',
                3 => 'DELETE'
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'page_size' => 10,
            'page_size_param' => 'page_size',
            'collection_class' => 'Zend\Paginator\Paginator',
            'entity_class' => 'Strapieno\PlaceGallery\Model\Entity\GalleryEntity'
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
                'route_identifier_name' => 'gallery_id',
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
