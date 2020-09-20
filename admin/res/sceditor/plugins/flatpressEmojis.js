

const EMOJIS = [
    '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', 
    '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', 
    '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', 
    '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣',
    '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', 
    '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', 
    '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', 
    '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', 
    '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', 
    '👿'
];

sceditor.command.set('emojis', {
    exec: function(caller) {

        const sceditorInsert = this.insert;

        const createEmojiLink = function(emoji) {
            let link = document.createElement('a');
            const linkText = document.createTextNode(emoji);
            link.appendChild(linkText);
            link.onclick = () => { sceditorInsert(emoji) };
            return link;
        };

        const emojisShowListContent = function() {
            let content = document.createElement('div');
            content.id = 'sceditor-Emojis';
            EMOJIS.forEach(function(emoji) {
                content.appendChild(createEmojiLink(emoji));
            });
            return content;
        };

        this.createDropDown(caller, 'emojis-list', emojisShowListContent());
    },
    tooltip: 'Insert a emoji emoticon'
});