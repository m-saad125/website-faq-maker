jQuery(document).ready(function($) {
    // Generate FAQs
    $('#wfm-generate-btn').on('click', function() {
        var btn = $(this);
        var spinner = btn.next('.spinner');
        var pageIds = [];
        
        $('input[name="wfm_pages[]"]:checked').each(function() {
            pageIds.push($(this).val());
        });

        var customContent = $('#wfm-custom-content').val();
        var count = $('#wfm-faq-count').val();

        if (pageIds.length === 0 && customContent.trim() === '') {
            alert('Please select at least one page or enter custom content.');
            return;
        }

        btn.prop('disabled', true);
        spinner.addClass('is-active');
        $('#wfm-results-section').hide();
        $('#wfm-generated-faqs').empty();

        $.ajax({
            url: wfm_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wfm_generate_faqs',
                nonce: wfm_vars.nonce,
                page_ids: pageIds,
                custom_content: customContent,
                count: count
            },
            success: function(response) {
                btn.prop('disabled', false);
                spinner.removeClass('is-active');

                if (response.success) {
                    renderFaqs(response.data);
                    $('#wfm-results-section').show();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                btn.prop('disabled', false);
                spinner.removeClass('is-active');
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Render FAQs for Preview
    function renderFaqs(faqs) {
        var container = $('#wfm-generated-faqs');
        faqs.forEach(function(faq, index) {
            var html = `
                <div class="wfm-preview-item" style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; background: #fff;">
                    <p>
                        <label><strong>Question:</strong></label><br>
                        <input type="text" class="wfm-preview-question" value="${escapeHtml(faq.question)}" style="width: 100%;">
                    </p>
                    <p>
                        <label><strong>Answer:</strong></label><br>
                        <textarea class="wfm-preview-answer" style="width: 100%;" rows="3">${escapeHtml(faq.answer)}</textarea>
                    </p>
                    <button type="button" class="button wfm-remove-preview" onclick="jQuery(this).parent().remove()">Remove</button>
                </div>
            `;
            container.append(html);
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Save FAQs
    $('#wfm-save-btn').on('click', function() {
        var btn = $(this);
        var title = $('#wfm-group-title').val();
        
        if (!title) {
            alert('Please enter a title for the FAQ Group.');
            return;
        }

        var faqs = [];
        $('.wfm-preview-item').each(function() {
            var q = $(this).find('.wfm-preview-question').val();
            var a = $(this).find('.wfm-preview-answer').val();
            if (q && a) {
                faqs.push({ question: q, answer: a });
            }
        });

        if (faqs.length === 0) {
            alert('No FAQs to save.');
            return;
        }

        btn.prop('disabled', true);
        btn.text('Saving...');

        $.ajax({
            url: wfm_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wfm_save_generated_faqs',
                nonce: wfm_vars.nonce,
                title: title,
                faqs: faqs
            },
            success: function(response) {
                btn.prop('disabled', false);
                btn.text('Save FAQ Group');

                if (response.success) {
                    alert('FAQ Group saved successfully!');
                    window.location.href = response.data.edit_url;
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                btn.prop('disabled', false);
                btn.text('Save FAQ Group');
                alert('An error occurred. Please try again.');
            }
        });
    });
});
