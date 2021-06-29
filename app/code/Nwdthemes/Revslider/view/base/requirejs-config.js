var config = {
    paths: {
        GreensockBase:          'Nwdthemes_Revslider/public/assets/js/tools/GreensockBase',
        touchSwipe:             'Nwdthemes_Revslider/public/assets/js/tools/TouchSwipe',
        punchgs:                'Nwdthemes_Revslider/public/assets/js/tools/punchgs',
        TweenMax:               'Nwdthemes_Revslider/public/assets/js/tools/TweenMax',
        TweenLite:              'Nwdthemes_Revslider/public/assets/js/tools/TweenMax',
        CustomBounce:           'Nwdthemes_Revslider/public/assets/js/tools/easing/CustomBounce',
        CustomEase:             'Nwdthemes_Revslider/public/assets/js/tools/easing/CustomEase',
        CustomWiggle:           'Nwdthemes_Revslider/public/assets/js/tools/easing/CustomWiggle',
        SplitText:              'Nwdthemes_Revslider/public/assets/js/tools/SplitText',
        waitForImages:          'Nwdthemes_Revslider/public/assets/js/tools/waitForImages',
        revolutionTools:        'Nwdthemes_Revslider/public/assets/js/revolution.tools.min',
        rs6:                    'Nwdthemes_Revslider/public/assets/js/rs6.min',
        vimeoPlayer:            'Nwdthemes_Revslider/public/assets/js/vimeo.player.min'
    },
    shim: {
        rs6: {
            deps: ['jquery', 'revolutionTools']
        },
        waitForImages: {
            deps: ['jquery']
        },
        CustomEase: {
            deps: ['punchgs']
        },
        CustomBounce: {
            deps: ['punchgs']
        },
        CustomWiggle: {
            deps: ['punchgs']
        },
        GreensockBase: {
            deps: ['punchgs']
        },
        SplitText: {
            deps: ['punchgs']
        },
        TweenMax: {
            deps: ['GreensockBase']
        }
    }
};