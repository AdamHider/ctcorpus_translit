<?php

defined('_JEXEC') or die;
?>
<div class="module-heading">
    <h2><?php echo JText::_('MOD_TITLE'); ?></h2>
    <p><?php echo JText::_('MOD_DESCRIPTION'); ?></p>
</div>
<div class="translit-main">
    <div class="tab">
      <button class="tablinks active tr-inline"  onclick="openTab(event, 'tr_inline')"><i class="fa fa-pencil-square-o fa-lg"></i> <?php echo JText::_('MOD_INLINE_MODE'); ?></button>
      <button class="tablinks tr-file" onclick="openTab(event, 'tr_file')"><i class=" fa fa-file-text-o fa-lg"></i> <?php echo JText::_('MOD_FILE_MODE'); ?></button>
    </div>

    <!-- Tab content -->
    <div id="tr_inline" style="display: block" class="tabcontent">
        <div class="g-grid">
            <div class="g-block size-46">
                <select class="select" id="inline_select_from">
                    <option value="crh-cyrl" selected><?php echo JText::_('MOD_CYRILLIC'); ?></option>
                    <option value="crh-latn"><?php echo JText::_('MOD_LATIN'); ?></option>
                </select>
                <div class="grow-wrap"> 
                    <textarea id="translit_inp" placeholder="<?php echo JText::_('MOD_TEXT_PLACEHOLDER'); ?>"></textarea>
                    <i class="char-counter"></i>
                    <a class="clear"><i class="fa fa-close  fa-lg"></i></a>
                </div>
            </div>
            <div class="g-block size-8" style="text-align: center"><a id="switch_langs"><i class="fa fa-exchange fa-lg"></i></a></div>
            <div class="g-block size-46">
                <select class="select" id="inline_select_to">
                    <option value="crh-cyrl" ><?php echo JText::_('MOD_CYRILLIC'); ?></option>
                    <option value="crh-latn" selected><?php echo JText::_('MOD_LATIN'); ?></option>
                </select>
                <div class="grow-wrap"> 
                    <textarea id="translit_out"  autosize></textarea>
                    <a class="copy-to-clip"><i class="fa fa-copy fa-lg"></i></a>
                </div>
            </div>
        </div>
       
       
    </div>
    
    <div id="tr_file" class="tabcontent">
        <form enctype="multipart/form-data" action="upload.php" method="post">
            <div class="drop-files">
                <input type="file" multiple name="file[]" id="file" onchange="updateList()" accept=".docx, .txt"/>
                <span><i class="fa fa-upload fa-lg"></i> <?php echo JText::_('MOD_CHOOSE_FILES'); ?> (.docx, .txt) (> 100MB)</span>
                <p style="margin: 0 0 10px; text-align: center; font-size: 12px;"><?php echo JText::_('MOD_CHOOSE_FILES_DESCR'); ?></p>
            </div>
            
            <div id="fileList"></div>
            
            <div id="uploadControl" style="display: none">
                <hr>
                <div>
                    <label for="file_select_to"><?php echo JText::_('MOD_TRANSLATE_TO'); ?> </label>
                    <select class="select" id="file_select_to">
                        <option value="crh-cyrl" ><?php echo JText::_('MOD_CYRILLIC'); ?></option>
                        <option value="crh-latn" selected><?php echo JText::_('MOD_LATIN'); ?></option>
                    </select>
                </div>
                <div style="text-align: center;  padding: 10px;">
                    <button id="transliterate_file" ><i class="fa fa-check fa-lg"></i> <?php echo JText::_('MOD_TRANSLITE_GO'); ?></button>
                    <div class="progress-container"  style="display: none; text-align: center">
                        <p class="progress-title">0%</p>
                        <progress id="loading" value="0" max="100"> 32% </progress>
                    </div>
                </div>  
                
            </div>
            <div id="file_zip" style="display: none">
                <hr>
                <div>
                    <h4><?php echo JText::_('MOD_SUCCESS_TITLE'); ?></h4>
                    <h6><?php echo JText::_('MOD_SUCCESS_DESCRIPTION'); ?></h6>
                </div>
                <div class="link-container">
                    
                </div>
                <a class="retry"><?php echo JText::_('MOD_TRY_ANOTHER'); ?></a>
            </div>
            <div id="error" style="display: none">
                <hr>
                <div class="link-container">
                    
                </div>
                <a class="retry"><?php echo JText::_('MOD_TRY_ANOTHER'); ?></a>
            </div>
            
        </form>
        <h4 style="margin: 0"><?php echo JText::_('MOD_TIP_HEADER'); ?></h4>
        <ul style="margin: 0 15px;">
            <li>
                <p style="font-size: 12px; margin: 0"><?php echo JText::_('MOD_TIP_1'); ?></p>
            </li>
            <li>
                <p style="font-size: 12px; margin: 0"><?php echo JText::_('MOD_TIP_2'); ?></p>
            </li>
            <li>
                <p style="font-size: 12px; margin: 0"><?php echo JText::_('MOD_TIP_3'); ?></p>
            </li>
        </ul>
    </div>
    
    

</div> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
    var totalsize = 0;
    var currentStep = 0;
    
    function init(){
        initControls()
    }
    function initControls(){
        jQuery('#translit_inp').on('input', (e) => { return transliterate() });
        jQuery('#transliterate_file').on('click', (e) => { uploadFiles(e) });
        jQuery('.retry').on('click', () => {
            jQuery('#file_zip').hide();
            jQuery('#fileList').html('')
            jQuery('#file').val('')
            
            
        })
        jQuery('#inline_select_from').on('change', (e) => {
            var value = jQuery(e.target).val();
            if(value == 'crh-cyrl') {
                jQuery('#inline_select_to').val('crh-latn')
            } else {
                jQuery('#inline_select_to').val('crh-cyrl')
            }
            transliterate()
        })
        jQuery('#inline_select_to').on('change', (e) => {
            var value = jQuery(e.target).val();
            if(value == 'crh-cyrl') {
                jQuery('#inline_select_from').val('crh-latn')
            } else {
                jQuery('#inline_select_from').val('crh-cyrl')
            }
            transliterate()
        })
        jQuery('#switch_langs').on('click', (e) => {
            if(jQuery('#inline_select_from').val() == 'crh-cyrl') {
                jQuery('#inline_select_from').val('crh-latn')
                jQuery('#inline_select_to').val('crh-cyrl')
            } else {
                jQuery('#inline_select_from').val('crh-cyrl')
                jQuery('#inline_select_to').val('crh-latn')
            }
            transliterate()
        })
        jQuery('.copy-to-clip').on('click', (e) => { copyToClipboard(e) });
        jQuery('.clear').on('click', (e) => { jQuery(e.target).closest('.grow-wrap').find('textarea').val(''); transliterate() });
        
    }

    function transliterate() {
        var value = jQuery('#translit_inp').val()
        if(value.length > 0){
            var firstLetter = Array.from(value)[0];
            if(firstLetter.search(/[а-яА-ЯёЁ]/i) > -1){
                jQuery('#inline_select_from').val('crh-cyrl')
                jQuery('#inline_select_to').val('crh-latn')
            } else {
                jQuery('#inline_select_from').val('crh-latn')
                jQuery('#inline_select_to').val('crh-cyrl')
            }
        }
        
        var letterCount = value.replace(/\s+/g, '').length;
        jQuery('.char-counter').html(letterCount);
        jQuery.ajax({
            url: "/index.php?option=com_ajax&module=translit&method=transliterate&format=json",
            type: "POST",
            data: {text: value, toVariant: jQuery('#inline_select_to').val()},
            success: function (response){
                jQuery('#translit_out').html(response.data.text);
                return true
            } 
        });
    }
    
    function updateTimer(part){
        if(totalsize == 0){
            return;
        }
        jQuery('.progress-container').show()
        var new_value = (part)*100/totalsize;
        if(new_value > 98) new_value = 98;
        jQuery('.progress-container progress').val(Math.ceil(new_value))
        jQuery('.progress-container .progress-title').html('<i class="fa fa-spin fa-spinner fa-lg"></i> '+Math.ceil(new_value)+'%')
    }
    function uploadFiles(e){
        e.preventDefault();
        
        jQuery('#transliterate_file').hide()
        var formData = new FormData($(e.target).parents('form')[0]);
        $.ajax({
            url: "/index.php?option=com_ajax&module=translit&method=uploadFiles&format=json",
            type: 'POST',
            success: function (response) {
                if(response.data){
                    jQuery('#fileList').show()
                    transliterateUploaded(response.data, 0);
                }
            },
            error: function(){
                jQuery('.progress-container progress').val(0)
                jQuery('.progress-container .progress-title').html('0%')
                jQuery('.progress-container').hide()
            },
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        });
    }
    function transliterateUploaded(hash, part) {
        updateTimer(part)
        jQuery('.drop-files').hide()
        jQuery('#fileList').hide()
        jQuery('#file').val('')
        jQuery('#transliterate_file i').attr('class', 'fa fa-spin fa-spinner fa-lg');
        
        jQuery.ajax({
            url: "/index.php?option=com_ajax&module=translit&method=transliterateUploaded&format=json",
            type: "POST",
            data: {hash: hash, toVariant: jQuery('#file_select_to').val(), part: part},
            error: function (e, response) {
                    jQuery('#transliterate_file').show()
                    jQuery('#transliterate_file i').attr('class', 'fa fa-check fa-lg');
                    jQuery('#error').show()
                    if(e.responseJSON && e.responseJSON.message){
                        jQuery('#error .link-container').html(e.responseJSON.message);
                    } else {
                        jQuery('#error .link-container').html("<?php echo JText::_('MOD_ERROR_TOO_BIG'); ?>");
                    }
                    jQuery('.drop-files').show()
                    jQuery('#uploadControl').hide()
                    jQuery('.progress-container progress').val(0)
                    jQuery('.progress-container .progress-title').html('0%')
                    jQuery('.progress-container').hide()
            },
            complete: function (response){
                if(response.data){
                    if(response.data.total_parts){
                        totalsize = response.data.total_parts
                    }
                    if(response.data.is_finished == false){
                        part++;
                        return transliterateUploaded(hash, part)
                    }
                    jQuery('#transliterate_file').show()
                    jQuery('#transliterate_file i').attr('class', 'fa fa-check fa-lg');
                    jQuery('#error').show()
                    if(e.responseJSON && e.responseJSON.message){
                        jQuery('#error .link-container').html(e.responseJSON.message);
                    } else {
                        jQuery('#error .link-container').html("<?php echo JText::_('MOD_ERROR_TOO_BIG'); ?>");
                    }
                    jQuery('.drop-files').show()
                    jQuery('#uploadControl').hide()
                    jQuery('.progress-container progress').val(0)
                    jQuery('.progress-container .progress-title').html('0%')
                    jQuery('.progress-container').hide()
                }
            },
            success: function (response){
                if(response.data == 'error:too_large'){
                    jQuery('#transliterate_file').show()
                    jQuery('#transliterate_file i').attr('class', 'fa fa-check fa-lg');
                    jQuery('#error').show()
                    jQuery('#error .link-container').html("<?php echo JText::_('MOD_ERROR_TOO_BIG'); ?>");
                    jQuery('.drop-files').show()
                    jQuery('#uploadControl').hide()
                    jQuery('.progress-container progress').val(0)
                    jQuery('.progress-container .progress-title').html('0%')
                    jQuery('.progress-container').hide()
                    return;
                }
                if(response.data){
                    if(response.data.total_parts){
                        totalsize = response.data.total_parts
                    }
                    if(response.data.is_finished == false){
                        part++;
                        return transliterateUploaded(hash, part)
                    }
                    jQuery('#error').hide()
                    jQuery('#transliterate_file').show()
                    jQuery('#transliterate_file i').attr('class', 'fa fa-check fa-lg');
                    jQuery('#file_zip .link-container').html("<a href='"+response.data.result+"' target='_blank'><i class='fa fa-download'></i> <?php echo JText::_('MOD_DOWNLOAD'); ?></a>");
                    jQuery('#file_zip').show()
                    jQuery('.drop-files').show()
                    jQuery('#uploadControl').hide()
                    jQuery('.progress-container progress').val(0)
                    jQuery('.progress-container .progress-title').html('0%')
                    jQuery('.progress-container').hide()
                }
            }
        });
    }

    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    } 
    updateList = function() {
        jQuery('#file_zip').hide();
        jQuery('#file_zip .link-container').html("");
        jQuery('#error').hide()
        var input = document.getElementById('file');
        var output = document.getElementById('fileList');
        var children = "";
        for (var i = 0; i < input.files.length; ++i) {
            children += '<li> <i class="fa fa-file-alt"></i> <b>' + input.files.item(i).name + '</b></li>';
        }
        output.innerHTML = '<ul>'+children+'</ul>';
        if(input.files.length > 0){
            jQuery('#uploadControl').show()
        } else {
            jQuery('#uploadControl').hide()
        }
    }
    function copyToClipboard (e) {
      // Get the text field
      
      var copyText = jQuery(e.target).closest('.grow-wrap').find('textarea').val()
    
    
       // Copy the text inside the text field
      navigator.clipboard.writeText(copyText);
    
    } 
    jQuery( document ).ready(function() {
        init()
    });
    
</script>
<style>
.translit-page #g-features{
    background: #f4f5f7;
    min-height: 70vh;
    padding: 0;
}
.translit-page #g-features h2,
.translit-page #g-features  h4{
    margin-bottom: 5px;
}
.translit-page #g-features h2 + p{
    margin-top: 5px;
}
.translit-page #g-features  h2{
    line-height: 1.2;
}
.translit-page #g-features  h6{
    margin-top: 5px;
}
.translit-page #g-features  a{
    cursor: pointer;
}
.translit-main{
    background: white;
}
 /* Style the tab */
.translit-main .tab {
  overflow: hidden;
  border: 1px solid #ccc;
}

/* Style the buttons that are used to open the tab content */
.translit-main .tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  text-transform: none;
}
#switch_langs{
    min-height: 40px;
    padding: 12px;
}
/* Change background color of buttons on hover */
.translit-main .tab button:hover {
  background-color: #24a0ce;
}

/* Create an active/current tablink class */
.translit-main .tab button.active {
  
  background: #24a0ce;
  color: white;
}
.grow-wrap{
    position: relative;
    display: flex;
    width: 101%;
}
.grow-wrap .copy-to-clip{
    position: absolute;
    bottom: 10px;
    right: 20px;
}
.grow-wrap .char-counter{
    position: absolute;
    bottom: 10px;
    right: 20px;
    color: gray;
}
.grow-wrap .clear{
    position: absolute;
    top: 10px;
    right: 20px;
}


/* Style the tab content */
.translit-main .tabcontent {
  display: none;
  padding: 12px ;
  border: 1px solid #ccc;
  border-top: none;
} 
.translit-main .drop-files{
    display: grid;
    align-items: center;
    background: #f9f9f9;
    position: relative;
    height: 100px;
    border: 1px solid lightgray;
    border-radius: 4px;
}
.translit-main .drop-files input{
    opacity: 0;
    position: absolute;
    width: 100%;
    height: 100%;
}
.translit-main .drop-files span{
    font-weight: bold;
    text-align: center;
}
.translit-main #fileList ul{
    list-style: none;
}
.grow-wrap::after {
  /* Note the weird space! Needed to preventy jumpy behavior */
  content: attr(data-replicated-value) " ";

  /* This is how textarea text behaves */
  white-space: pre-wrap;

  /* Hidden from view, clicks, and screen readers */
  visibility: hidden;
}
.translit-main textarea{
    min-height: 350px;
    padding-right: 40px !important;
    scrollbar-width: thin;
}
.translit-main .g-block{
    padding: 10px;
    display: flex;
    flex-direction: column;
}
.translit-main select {
    min-width: 200px;
    height: 40px;
    margin: 10px 0;
    border-width: 2px;
    border-radius: 3px;
    min-height: 40px;
}
.translit-main #translit_out{
    background: #f9f9f9;
    height: 100%;
    border: 1px solid lightgray;
    border-radius: 4px;
    padding: 0.375rem 1.25rem;

}

.translit-main button{
    padding: 0.55rem 1.45rem;
    border: 1px solid #0f97df;
    background: transparent;
    color: #0f97df;
    font-weight: bold;
    transition: 0.3s all;
    text-transform: uppercase;
}
.translit-main button:hover{
    background-color: #0f97df;
    color: white;
}
#file_zip{
    text-align: center;
}
#file_zip .link-container a{
  padding: 0.55rem 1.45rem;
  border: 1px solid #09aa22;
  background: transparent;
  color: #09aa22;
  font-weight: bold;
  text-transform: uppercase;
  display: inline-block;
}
#file_zip .link-container a:hover{
    background-color: #09aa22;
    color: white;
}
@media only screen and (max-width: 740px) {
    .translit-page #g-navigation{
        min-height: 80px;
        background: #312f38 !important;
    }
    .translit-main textarea{
        min-height: 250px;
    }
    .translit-page #g-features .g-content{
        margin: 0;
        padding: 0;
    }
    .translit-page .module-heading{
        padding: 1em;
    }
}
#error{
    font-weight: bold;
    color: darkred;
}
</style>

