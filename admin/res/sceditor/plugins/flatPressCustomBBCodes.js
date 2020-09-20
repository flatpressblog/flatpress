/* Include a headers and IMG fix */

// IMG copied and modified from format/bbcode.js
sceditor.formats.bbcode.set('img', {
    allowsEmpty: true,
    tags: {
        img: {
            src: null
        }
    },
    allowedChildren: ['#'],
    quoteType: sceditor.BBCodeParser.QuoteType.never,
    format: function (element, content) {
        var	width, height,
            attribs   = '',
            style     = function (name) {
                return element.style ? element.style[name] : null;
            };

        const EMOTICON_DATA_ATTR = 'data-sceditor-emoticon';

        // check if this is an emoticon image
        if (sceditor.dom.attr(element, EMOTICON_DATA_ATTR)) {
            return content;
        }

        width = sceditor.dom.attr(element, 'width') || style('width');
        height = sceditor.dom.attr(element, 'height') || style('height');

        // only add width and height if one is specified
        if ((element.complete && (width || height)) ||
            (width && height)) {

            attribs = '=' + sceditor.dom.width(element) + 'x' +
                sceditor.dom.height(element);
        }

        return '[img=' + sceditor.dom.attr(element, 'src') + '' + attribs + ']' + '[/img]';
    },
    html: function (token, attrs, content) {
        var	undef, width, height, match,
            attribs = '';

        // handle [img width=340 height=240]url[/img]
        width  = attrs.width;
        height = attrs.height;

        // handle [img=340x240]url[/img]
        if (attrs.defaultattr) {
            match = attrs.defaultattr.split(/x/i);

            width  = match[0];
            height = (match.length === 2 ? match[1] : match[0]);
        }

        if (width !== undef) {
            attribs += ' width="' + sceditor.escapeEntities(width, true) + '"';
        }

        if (height !== undef) {
            attribs += ' height="' + sceditor.escapeEntities(height, true) + '"';
        }

        if(/^images/.test(content)) { // Fix small bug with fp-content directory
            return '<img' + attribs + ' src="fp-content/' + sceditor.escapeUriScheme(content) + '" />';
        } else {
            return '<img' + attribs + ' src="' + sceditor.escapeUriScheme(content) + '" />';
        }
    }
});

// Header tag (From h1 to h6)

for(let i = 1; i < 7; ++i) { // Headers: h1 to h6
    sceditor.formats.bbcode.set('h' + i, {
        tags: {
            ['h' + i] : null,
        },
        isInline: false,
        format: '[h'+ i +']{0}[/h' + i + ']',
        html: '<h' + i + '>{0}</h' + i + '>',
    });
}