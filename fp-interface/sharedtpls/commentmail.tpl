{entries}
{entry}
Dear {$flatpress.AUTHOR},

{$maildata.name|stripslashes} has just posted a comment to the entry entitled "{$subject}

This is the commentlink to the entry:
{$flatpress.WWW}comments.php?entry={$id}

Here is the comment that has just been posted:
***************
{$maildata.content|stripslashes}
***************

All the best,
{$flatpress.TITLE}
{/entry}
{rewind_posts}
{/entries}

