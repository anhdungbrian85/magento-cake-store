define([
    'jquery',
    'domReady!'
], function($){
    'use strict';
    $.widget('cakebox.uploadImage', {
          _create: function() {
               let $widget = this;
               var element = $widget.element,
               input = element.find('[type="file"]');


               input.on( 'change', function(e) {
                    var file = this.files;
                    if ( file.length > 0 ) {
                         var src = URL.createObjectURL(e.target.files[0]),
                              html = '';

                         html += '<img class="image-preview" src="' + src + '" /><span class="file-preview-name">'
                              + file[0].name +
                              '</span><span class="icon-delete-file"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path id="Path_674" data-name="Path 674" d="M7,14A7,7,0,0,1,2.049,2.049a7,7,0,0,1,9.9,9.9A6.956,6.956,0,0,1,7,14ZM7,7.987H7L9.023,10.01a.694.694,0,0,0,.986,0,.712.712,0,0,0,0-.986L7.987,7,10.01,4.977a.69.69,0,0,0,.151-.227.7.7,0,0,0,.053-.266A.7.7,0,0,0,9.023,3.99L7,6.013,4.977,3.99a.7.7,0,0,0-.987.987L6.013,7,3.99,9.023a.7.7,0,0,0,.987.986L7,7.987Z" fill="#f65252"/></svg></span>';

                         element.find('.file-uploaded').html(html);
                         element.find('.file-label').hide();
                              // for (let file of this.files) {
                              //      dt.items.add(file);
                              // }
                    } else {
                         element.find('.file-uploaded').html('');
                         element.find('.file-label').show();
                    }
               } );

               $('.icon-delete-file').on('click', function(e) {
                    e.preventDefault();
                    input.val('');
               });
          }
    });

    return $.cakebox.uploadImage;
});
