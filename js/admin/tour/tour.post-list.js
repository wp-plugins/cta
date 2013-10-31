// Post Edit Screen Tour 
   jQuery(".thumbnail-lander.column-thumbnail-lander:eq(0)").attr({"data-step": "1", "data-intro": 'This is a quick screenshot of your landing page.<br><br> Click on it to see the live version in a popup window here.<br><br> You can preview your a/b variations directly from this view.'});
   jQuery(".post-title.page-title.column-title:eq(0)").attr({"data-step": "2", "data-intro": '<p>This is the admin title of the landing page. This isn\'t visible to visitors</p><p>You can delete, clone, or clears all of the page stats from here.</p><p><strong>Trash:</strong> Deletes this landing page and places it in the trash</p><p><strong>Edit:</strong> Jump to edit screen of this landing page</p><p><strong>Preview:</strong> Pop open preview window</p><p><strong>Clone:</strong> Clones exact copy of landing page. This includes all current variations.</p><p><strong>Clear Stats:</strong> This will clear all stats for each variation. This cannot be undone.</p>'});
    jQuery(".stats.column-stats:eq(0)").attr({"data-step": "3", "data-intro": '<p>These are the current stats of your landing page variations.</p><p>If you <strong>hover over the letter</strong> you will be able to preview or jump directly to the edit screen of that particular variation.</p><p>You can also clear indiviudal variation stats from there.</p>'});
     jQuery("#impressions").attr({"data-step": "4", "data-intro": '<p>This column shows the total number of page views the landing page has. This include all current A/B testing variations.</p>'});
     jQuery("#actions").attr({"data-step": "5", "data-intro": '<p>This column shows the total number of conversions the landing page has. This include all current A/B testing variations.</p>'});
     jQuery("#cr").attr({"data-step": "6", "data-intro": '<p>This column shows the total aggregate conversion rate of the landing page. This include all current A/B testing variations.</p>'});
     jQuery(".add-new-h2").attr({"data-step": "7", "data-intro": '<p>Thats all folks! Go ahead and create a new landing page and get started!</p>'});