function bindEditableTableForm(formSelector, tableSelector, outputSelector, extraFields = {}) {
    $(formSelector).on('submit', function (e) {
        const formData = { table_data: {} };

        // Add extra fields
        for (const key in extraFields) {
            formData[key] = $(extraFields[key]).val();
        }

        const table = $(tableSelector).DataTable();

        table.rows().every(function () {
            const row = this.node();
            let rowData = {};
            let trn_id_hash = '';

            // contenteditable
            $(row).find('td[contenteditable="true"]').each(function () {
                const name = $(this).data('name');
                trn_id_hash = $(this).data('trn_id_hash');
                if (name) rowData[name] = $(this).text().trim();
            });

            // input
            $(row).find('input').each(function () {
                const $input = $(this);
                const name = $input.data('name');
                trn_id_hash = $input.data('trn_id_hash') || trn_id_hash;

                if (!name || !trn_id_hash) return;

                if ($input.is(':checkbox')) {
                    rowData[name] = $input.is(':checked') ? 'Y' : 'N';
                } else if ($input.attr('type') === 'date') {
                    rowData[name] = $input.val();
                } else if ($input.attr('type') === 'number') {
                    rowData[name] = parseFloat($input.val()) || 0;
                } else {
                    rowData[name] = $input.val();
                }
            });

            // select
            $(row).find('select').each(function () {
                const $select = $(this);
                const name = $select.data('name');
                trn_id_hash = $select.data('trn_id_hash') || trn_id_hash;

                if (!name || !trn_id_hash) return;

                rowData[name] = $select.val();
            });

            if (trn_id_hash) {
                formData.table_data[trn_id_hash] = rowData;
            }
        });

        $(outputSelector).val(JSON.stringify(formData));
        console.log("Submitted:", formData);
    });
}
