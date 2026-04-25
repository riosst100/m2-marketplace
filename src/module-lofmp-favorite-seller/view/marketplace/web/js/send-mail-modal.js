require([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'jquery.tagsinput'
], function ($, alert, modal) {

    let sendMailModal;

    window.openSendMailModal = function () {
        const emailList = [];

        // Detect email column index dynamically
        const emailColIndex = $('table.data-grid thead th').filter(function () {
            return $(this).text().trim().toLowerCase() === 'email';
        }).index();

        // Collect selected rows' emails
        $('table.data-grid tbody tr').each(function () {
            const $row = $(this);
            const $checkbox = $row.find('input[data-action="select-row"]');
            if ($checkbox.is(':checked')) {
                const email = $row.find('td').eq(emailColIndex).find('.data-grid-cell-content').text().trim();
                if (email) {
                    emailList.push(email);
                }
            }
        });

        if (emailList.length === 0) {
            alert({
                content: $.mage.__('Please select at least one follower.')
            });
            
            return;
        }

        // Create modal once
        if (!sendMailModal) {
            sendMailModal = modal({
                type: 'popup',
                title: $.mage.__('Send Newsletter to Followers'),
                responsive: true,
                innerScroll: true,
                buttons: []
            }, $('#send-mail-modal'));

            // Initialize TinyMCE only once
            if (typeof tinyMCE !== 'undefined' && !tinyMCE.get('email-content')) {
                tinyMCE.init({
                    selector: '#email-content',
                    menubar: false,
                    branding: false,
                    height: 250,
                    plugins: 'link lists image code',
                    toolbar: 'undo redo | bold italic underline | bullist numlist | link image code',
                    file_picker_callback: function (callback, value, meta) {
                        if (meta.filetype === 'image') {
                            $('#upload').trigger('click');
                            $('#upload').on('change', function () {
                                const file = this.files[0];
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    callback(e.target.result, { alt: '' });
                                };
                                reader.readAsDataURL(file);
                            });
                        }
                    }
                });
            }
        }

//         const maxEmails = 10;
// const limitedEmails = emailList.slice(0, maxEmails);

//         // Refresh tag list
//         $('#input-tags').importTags('');
//         $('#input-tags').tagsInput({
//             width: '450px',
//             interactive: false,
//             tagClass: 'tags tag-value'
//         }).importTags(limitedEmails.toString());

        // Misalnya emailList adalah array dari email yang dipilih
const maxEmails = 8;
const totalEmails = emailList.length;

// Batasi hanya 10 email pertama
const limitedEmails = emailList.slice(0, maxEmails);

// Hapus tag sebelumnya
$('#input-tags').importTags('');

// Inisialisasi tags input
$('#input-tags').tagsInput({
    width: '450px',
    interactive: false,
    tagClass: 'tags tag-value'
});

// Import email yang dibatasi
$('#input-tags').importTags(limitedEmails.toString());

// Kalau ada sisa email, tambahkan tag “+X more”
if (totalEmails > maxEmails) {
    const remaining = totalEmails - maxEmails;
    const moreTag = `+${remaining} more`;

    // Tambahkan tag manual untuk indikator sisa
    $('#input-tags').addTag(moreTag);
}

        // Open modal
        $('#send-mail-modal').modal('openModal');
    };

    // Handle send button
    $(document).on('click', '#send-message', function (e) {
        e.preventDefault();
        
        const $loader = $('#loading-animation');
        $loader.css('visibility', 'visible');

        const subject = $('#email-subject').val();
        const content = tinyMCE.get('email-content') ? tinyMCE.get('email-content').getContent() : $('#email-content').val();

        // Gather emails
        const emailAddresses = [];
        $('#input-tags').siblings('.tagsinput').find('.tag').each(function () {
            const email = $(this).text().slice(0, -1).trim();
            if (email) {
                emailAddresses.push(email);
            }
        });

        if (emailAddresses.length === 0) {
            $('#send-mail-modal').modal('closeModal');
            alert({
                content: $.mage.__('Please select at least one recipient.')
            });
            $loader.css('visibility', 'hidden');
            return;
        }

        // Disable button during send
        const $button = $(this);
        $button.prop('disabled', true).text($.mage.__('Sending...'));

        // AJAX with better error and success handling
        $.ajax({
            url: BASE_URL + 'marketplace/favoriteseller/action/sendmessage',
            type: 'POST',
            dataType: 'json',
            data: {
                email_addresses: emailAddresses,
                subject: subject,
                content: content
            },
            timeout: 20000, // 20s timeout protection
            success: function (response) {
                $loader.css('visibility', 'hidden');
                $button.prop('disabled', false).text($.mage.__('Send Message'));

                if (response && response.status == "success") {
                    alert({
                        content: $.mage.__('Your newsletter was sent successfully to the selected recipients.')
                    });
                    
                    $('#send-mail-modal').modal('closeModal');
                    $('#email-subject').val('');
                    if (tinyMCE.get('email-content')) {
                        tinyMCE.get('email-content').setContent('');
                    }
                } else {
                    alert({
                        content: $.mage.__('Server did not confirm success.')
                    });
                    console.warn('Response:', response);
                }
            },
            error: function (xhr, status, errorThrown) {
                $loader.css('visibility', 'hidden');
                $button.prop('disabled', false).text($.mage.__('Send Message'));

                if (status === 'timeout') {
                    alert({
                        content: $.mage.__('Server did not confirm success.')
                    });
                } else {
                    alert({
                        content: $.mage.__('Error while sending message.')
                    });
                }
                console.error('Send message failed:', status, errorThrown);
            },
            complete: function (xhr, status) {
                console.log('Send message completed with status:', status);
            }
        });
    });

});
