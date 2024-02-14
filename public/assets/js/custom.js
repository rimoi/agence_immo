$(function () {

    $('.chat_message_messageFile_file').on('click', function (){
        $('.js-upload-file').trigger('click');
    })

    $(".custom-file-input").on("change", function () {

        let fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);

        let i = $(this).prev('label').clone();
        let file = $(".custom-file-input")[0].files[0].name;
        let label = $(".label");
        label.text(file);
    });

    if ($('#message-container').length) {
        let messageContainer = document.getElementById('message-container');
        messageContainer.scrollTop = messageContainer.scrollHeight;
    }

    if ($('.js-select2').length) {
        let input_select2 = $('.js-select2');
        input_select2.select2({
            allowClear: true,
            placeholder: input_select2.attr('placeholder'),
        });
    }

    $('.js-show-element').on('click', function (e) {
        e.preventDefault();

        let element_class = $(this).data('classParent');
        $(this).closest('.js-macro-upload-image').find(element_class).toggleClass('d-none');

        $(this).closest('.js-parent-element').addClass('d-none');
    });


    $('.add-another-collection-widget').click(function (e) {

        var elementIndex = [];
        // Générer les données
        for (var i = 0; i < 20; i++) {
            var value = String.fromCharCode(65 + i % 26) + String.fromCharCode(65 + i % 26); // Ajoute une lettre à chaque chiffre
            elementIndex.push(value);
        }

        var list = $($(this).attr('data-list-selector'));
        // Try to find the counter of the list or use the length of the list
        var counter = list.data('widget-counter') || list.children().length;

        // grab the prototype template
        var newWidget = list.attr('data-prototype');
        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        // newWidget = newWidget.replace(/__name__/g, elementIndex[counter]);
        newWidget = newWidget.replace(/__name__/g, elementIndex[counter].toLowerCase());
        // newWidget = newWidget.replace(/__name__/g, counter);
        // Increase the counter
        counter++;
        // And store it, the length cannot be used if deleting widgets is allowed
        list.data('widget-counter', counter);

        // create a new list element and add it to the list
        var newElem = $(list.attr('data-widget-tags')).html(newWidget);
        newElem.appendTo(list);

        addTagFormDeleteLink(newElem);
    });

    function addTagFormDeleteLink(item) {

        const div = document.createElement('div');
        div.className = 'col-12 col-sm-2 text-center text-sm-left';

        const removeFormButton = document.createElement('button');
        removeFormButton.innerText = 'Supprimer';
        removeFormButton.className = 'btn-sm btn-danger mt-2';
        removeFormButton.title= 'Supprimer cette ligne'

        div.append(removeFormButton);

        item.append(div);

        removeFormButton.addEventListener('click', (e) => {
            e.preventDefault();
            // remove the li for the tag form
            item.remove();
        });
    }

    document
        .querySelectorAll('#image-fields-list .js-genus-scientist-item')
        .forEach((tag) => {
            addTagFormDeleteLink(tag)
        });

    $('.js-show-element').on('click', function (e) {
        e.preventDefault();

        let element_class = $(this).data('classParent');

        $(this).closest('.js-macro-upload-image').find(element_class).removeClass('d-none');

        $(this).closest('.js-parent-element').addClass('d-none');
    });
});
