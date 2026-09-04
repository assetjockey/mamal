class AltumUploader {

    constructor(options, translations) {
        /* Provided configuration options */
        this.options = {
            /* Use this to handle file uploads directly to S3 storage */
            is_direct_offload_upload: true,

            /* Selector for the upload button */
            upload_button_selector: null,

            /* Selector for the folder upload button */
            upload_folder_button_selector: null,

            /* Allowed drag & drop zone */
            upload_area_selector: null,

            /* Auto upload */
            auto_upload: true,

            /* Auto form submission */
            auto_form_submission: false,

            /* Parallel uploading */
            parallel_file_uploading: false,

            /* Maximum number of files to upload in parallel */
            parallel_upload_limit: 3,

            /* How many files can be selected */
            files_per_transfer_limit: 1,

            /* Total size in bytes per transfer */
            transfer_size_limit: 2 * 1000 * 1000,

            /* Storage usage by the current user */
            current_storage_size_usage: 0,

            /* Maximum allowed storage */
            storage_size_limit: -1,

            /* Send files as chunks */
            upload_in_chunks: false,

            /* If yes */
            chunk_size_limit: 2 * 1000 * 1000,

            /* Upload file endpoint */
            upload_file_endpoint_url: null,

            /* Upload file endpoint extra params */
            upload_file_endpoint_params: {},

            /* Delete file endpoint */
            delete_file_endpoint_url: null,

            /* Upload file endpoint extra params */
            delete_file_endpoint_params: {},

            /* Blacklisted file extensions: comma separated values */
            blacklisted_file_extensions: null,

            ...options
        };

        /* Translations */
        this.translations = {
            blacklisted_file_extension: 'You are not allowed to upload files of this type.',
            files_per_transfer_limit: `You can not select more than %s files per transfer.`,
            transfer_size_limit: `You can not upload more than %s per transfer.`,
            storage_size_limit: `You can not upload more than %s in total.`,
            duplicate_file: `This file has already been added.`,
            ...translations
        };

        /* Files */
        this.files = {};

        /* Hidden file input */
        this.hidden_file_input = null;
        this.hidden_folder_file_input = null;

        /* Extra vars */
        this.extra = {
            total_size: 0,
            total_files: 0,
            total_uploaded_size: 0,
            total_upload_progress: 0,
        };

        /* Available events to stash callbacks for events */
        this.events = {
            added_file: [],
            uploading_file: [],
            upload_file_error: [],
            upload_file_success: [],
            removed_file: [],
            removed_files: [],
            uploading_files: [],
            finished_uploading_files : []
        };

        this.initiate();
    }

    initiate() {
        /* Parse blacklisted file extensions */
        if(this.options.blacklisted_file_extensions) {
            this.options.blacklisted_file_extensions = this.options.blacklisted_file_extensions.split(',');
        }

        this.initiate_hidden_file_input();
        this.initiate_hidden_folder_file_input();

        this.initiate_upload_button();
        this.initiate_upload_folder_button();

        this.initiate_upload_area();
    }

    initiate_hidden_file_input() {
        this.hidden_file_input = document.createElement('input');
        this.hidden_file_input.setAttribute('type', 'file');
        this.hidden_file_input.setAttribute('tabindex', '-1');

        if(this.options.files_per_transfer_limit == -1 || this.options.files_per_transfer_limit > 1) {
            this.hidden_file_input.setAttribute('multiple', 'multiple');
        }

        /* Do not display the input */
        this.hidden_file_input.style.display = 'none';
        this.hidden_file_input.style.visibility = 'hidden';

        /* Event listener */
        this.hidden_file_input.addEventListener('change', async event => {
            if(this.hidden_file_input.files.length) {
                for(let file of this.hidden_file_input.files) {

                    /* Try to add file and cancel the rest if there is an error */
                    if(!this.add_file(file)) {
                        break;
                    }

                }

                /* Auto upload & form submission */
                if(this.options.auto_upload) {
                    await this.upload_files();

                    if(this.options.auto_form_submission) {
                        document.querySelector(this.options.auto_form_submission).requestSubmit();
                    }
                }
            }
        });
    }

    initiate_hidden_folder_file_input() {
        if(!this.options.upload_folder_button_selector) {
            return;
        }

        this.hidden_folder_file_input = document.createElement('input');
        this.hidden_folder_file_input.setAttribute('type', 'file');
        this.hidden_folder_file_input.setAttribute('tabindex', '-1');
        this.hidden_folder_file_input.setAttribute('webkitdirectory', 'webkitdirectory');

        if(this.options.files_per_transfer_limit == -1 || this.options.files_per_transfer_limit > 1) {
            this.hidden_folder_file_input.setAttribute('multiple', 'multiple');
        }

        /* Do not display the input */
        this.hidden_folder_file_input.style.display = 'none';
        this.hidden_folder_file_input.style.visibility = 'hidden';

        /* Event listener */
        this.hidden_folder_file_input.addEventListener('change', async event => {
            if(this.hidden_folder_file_input.files.length) {
                for(let file of this.hidden_folder_file_input.files) {

                    /* Try to add file and cancel the rest if there is an error */
                    if(!this.add_file(file)) {
                        break;
                    }

                }

                /* Auto upload & form submission */
                if(this.options.auto_upload) {
                    await this.upload_files();

                    if(this.options.auto_form_submission) {
                        document.querySelector(this.options.auto_form_submission).requestSubmit();
                    }
                }
            }
        });
    }

    initiate_upload_button() {
        /* Check for any potential problems */
        if(!this.options.upload_button_selector || (this.options.upload_button_selector && !document.querySelector(this.options.upload_button_selector))) {
            throw new Error('You must provide a valid & existing upload button element selector.')
        }

        /* Click the hidden file input handler */
        document.querySelector(this.options.upload_button_selector).addEventListener('click', event => {
            this.hidden_file_input.click();
        });
    }

    initiate_upload_folder_button() {
        if(!this.options.upload_folder_button_selector) {
            return;
        }

        /* Click the hidden file input handler */
        document.querySelector(this.options.upload_folder_button_selector).addEventListener('click', event => {
            this.hidden_folder_file_input.click();
        });
    }

    initiate_upload_area() {
        /* Check for any potential problems */
        if(!this.options.upload_area_selector || (this.options.upload_area_selector && !document.querySelector(this.options.upload_area_selector))) {
            throw new Error('You must provide a valid & existing upload area element selector.')
        }

        /* Add handlers */
        document.querySelector(this.options.upload_area_selector).addEventListener('drop', async event => {
            event.preventDefault();

            const process_item_entry = async (item_entry) => {
                if(item_entry.isFile) {
                    const file = await new Promise(resolve => item_entry.file(resolve));
                    const result = this.add_file(file);
                    if(!result) throw new Error(':)');
                }

                if(item_entry.isDirectory) {
                    const reader = item_entry.createReader();
                    let entries = [];

                    const read_all_entries = () => new Promise(resolve => {
                        const read_batch = () => {
                            reader.readEntries(batch => {
                                if(batch.length) {
                                    entries = entries.concat(batch);
                                    read_batch();
                                } else {
                                    resolve(entries);
                                }
                            });
                        };
                        read_batch();
                    });

                    const all_entries = await read_all_entries();

                    for (let entry of all_entries) {
                        await process_item_entry(entry);
                    }
                }
            };

            try {
                const promises = [];

                for (let item of event.dataTransfer.items) {
                    const item_entry = item.webkitGetAsEntry();
                    if(item_entry) {
                        promises.push(process_item_entry(item_entry));
                    }
                }

                await Promise.all(promises);
            } catch (error) {
                console.error('Error processing drop:', error);
            }

            /* Auto upload & form submission */
            if(this.options.auto_upload) {
                await this.upload_files();

                if(this.options.auto_form_submission) {
                    document.querySelector(this.options.auto_form_submission).requestSubmit();
                }
            }
        });

        document.querySelector(this.options.upload_area_selector).addEventListener('dragover', event => {
            event.preventDefault();
        });

        /* Support for copy pasting */
        document.addEventListener('paste', async event => {
            if(document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') {
                return;
            }

            let clipboard_items = event.clipboardData.items;
            let file_pasted = false;

            for(let index = 0; index < clipboard_items.length; index++) {
                let clipboard_item = clipboard_items[index];

                if(clipboard_item.kind === 'file') {
                    let file = clipboard_item.getAsFile();
                    this.add_file(file);
                    file_pasted = true;
                }
            }

            if(file_pasted) {
                /* Auto upload & form submission */
                if(this.options.auto_upload) {
                    await this.upload_files();

                    if(this.options.auto_form_submission) {
                        document.querySelector(this.options.auto_form_submission).requestSubmit();
                    }
                }
            }

            /* If no files were pasted, paste text as .txt file */
            if(!file_pasted) {
                let pasted_text = event.clipboardData.getData('text/plain');

                if(pasted_text.trim() !== '') {
                    /* Format timestamp like "text-2025-06-07 12:10:15.txt" */
                    let current_datetime = new Date();
                    let year = current_datetime.getFullYear();
                    let month = String(current_datetime.getMonth() + 1).padStart(2, '0');
                    let day = String(current_datetime.getDate()).padStart(2, '0');
                    let hours = String(current_datetime.getHours()).padStart(2, '0');
                    let minutes = String(current_datetime.getMinutes()).padStart(2, '0');
                    let seconds = String(current_datetime.getSeconds()).padStart(2, '0');

                    let file_name = `text-${year}-${month}-${day} ${hours}:${minutes}:${seconds}.txt`;

                    let blob = new Blob([pasted_text], { type: 'text/plain' });
                    let generated_file = new File([blob], file_name, { type: 'text/plain' });

                    this.add_file(generated_file);

                    /* Auto upload & form submission */
                    if(this.options.auto_upload) {
                        await this.upload_files();

                        if(this.options.auto_form_submission) {
                            document.querySelector(this.options.auto_form_submission).requestSubmit();
                        }
                    }
                }
            }
        });
    }

    add_file(file, alerts = true) {
        /* Prevent duplicate files (same name and size) */
        for(let file_uuid in this.files) {
            let existing_file = this.files[file_uuid];
            if(existing_file.name === file.name && existing_file.size === file.size) {
                if(alerts) alert(this.translations.duplicate_file);
                return false;
            }
        }

        /* Check for the files per transfer limit */
        if(this.options.files_per_transfer_limit != -1 && this.extra.total_files >= this.options.files_per_transfer_limit) {
            if(alerts) alert(this.translations.files_per_transfer_limit.replace('%s', this.options.files_per_transfer_limit));
            return false;
        }

        /* Check for blacklisted file extensions */
        if(this.options.blacklisted_file_extensions.length) {
            for (let blacklisted_file_extension of this.options.blacklisted_file_extensions) {
                if(file.name.toLowerCase().endsWith(blacklisted_file_extension)) {
                    alert(this.translations.blacklisted_file_extension);
                    return false;
                }
            }
        }

        /* Check for the transfer size limit */
        if(this.extra.total_size + file.size >= this.options.transfer_size_limit && this.options.transfer_size_limit != -1) {
            if(alerts) alert(this.translations.transfer_size_limit.replace('%s', this.get_formatted_bytes(this.options.transfer_size_limit)));
            return false;
        }

        /* Check for the total storage size */
        if(this.options.current_storage_size_usage + this.extra.total_size + file.size >= this.options.storage_size_limit && this.options.storage_size_limit != -1) {
            if(alerts) alert(this.translations.storage_size_limit.replace('%s', this.get_formatted_bytes(this.options.storage_size_limit)));
            return false;
        }

        /* Append new data */
        file.altum = {
            uuid: this.generate_uuid(),
            uploaded_size: 0,
            upload_progress: 0,
        }

        /* Append file to the main object */
        this.files[file.altum.uuid] = file;

        /* Update stats */
        this.extra.total_size += file.size;
        this.extra.total_files += 1;

        /* Emit event */
        this.emit('added_file', file);

        return true;
    }

    async remove_file(file_uuid) {
        if(!this.files[file_uuid]) {
            return;
        }

        let file = this.files[file_uuid];

        /* Delete entry */
        delete this.files[file_uuid];

        /* Send delete request if needed */
        if(file.altum.upload_progress > 0 && this.options.delete_file_endpoint_url) {

            /* Delete the uploaded chunks */
            const form = new FormData();
            form.set('uuid', file_uuid);
            for(let [key, value] of Object.entries(this.options.delete_file_endpoint_params)) {
                form.set(key, value);
            }

            /* Send request to server */
            let response = fetch(this.options.delete_file_endpoint_url, {
                method: 'post',
                body: form
            });

        }

        /* Update stats */
        this.extra.total_size -= file.size;
        this.extra.total_files -= 1;

        /* Emit event */
        this.emit('removed_file', file);

        /* Update total stats */
        this.extra.total_uploaded_size = 0;
        for(let [key, f] of Object.entries(this.files)) {
            this.extra.total_uploaded_size += f.altum.uploaded_size;
        }
        this.extra.total_upload_progress = (this.extra.total_uploaded_size * 100 / this.extra.total_size).toFixed(2);
    }

    remove_files() {
        for(let [key, file] of Object.entries(this.files)) {
            this.remove_file(file.altum.uuid);
        }

        /* Emit event */
        this.emit('removed_files', this.files);
    }

    /* Data sending */
    async upload_files() {
        /* Emit event */
        this.emit('uploading_files');

        /* Only upload files not yet uploaded */
        let pending_files = Object.values(this.files).filter(file => file.altum.upload_progress < 100);

        if(pending_files.length === 0) {
            this.emit('finished_uploading_files');
            window.removeEventListener('beforeunload', this.alert_when_closing_window);
            return true;
        }

        /* How many files are we uploading? */
        let total_files = this.extra.total_files;

        /* Alert user if he tries to close tab */
        window.addEventListener('beforeunload', this.alert_when_closing_window);

        let upload_promises = [];

        for(let [key, file] of Object.entries(this.files)) {
            /* Make sure the file still exists as it may get deleted while uploading */
            if(this.files[file.altum.uuid] === undefined) {
                continue;
            }

            if(this.options.parallel_file_uploading) {

                /* Upload files in batches of parallel_upload_limit */
                let index = 0;
                let batch_size = this.options.parallel_upload_limit;
                while(index < pending_files.length) {
                    let batch = pending_files.slice(index, index + batch_size);
                    let results = await Promise.all(batch.map(file => this.upload_file(file)));

                    /* If any upload failed, stop */
                    if(results.includes(false)) {
                        window.removeEventListener('beforeunload', this.alert_when_closing_window);
                        return false;
                    }

                    index += batch_size;
                }

            } else {
                let upload_file = await this.upload_file(file);

                if(!upload_file) {
                    /* Remove tab closing event listener */
                    window.removeEventListener('beforeunload', this.alert_when_closing_window);

                    return false;
                };
            }
        }

        /* Remove tab closing event listener */
        window.removeEventListener('beforeunload', this.alert_when_closing_window);

        /* If the total files number changed, go again */
        if(total_files < this.extra.total_files) {
            return await this.upload_files();
        }

        /* Emit event */
        this.emit('finished_uploading_files');

        return true;
    }

    async upload_file(file) {
        /* Check for any potential problems */
        if(!this.options.upload_file_endpoint_url) {
            throw new Error('You must provide a valid & existing upload file endpoint URL.')
        }

        /* Make sure the file is not already uploaded */
        if(file.altum.upload_progress == 100) {
            return true;
        }

        /* Direct file upload offload */
        if(this.options.is_direct_offload_upload) {
            /* Detect chunking */
            let chunk_size_limit = 5 * 1024 * 1024;
            let total_chunks = Math.ceil(file.size / chunk_size_limit);
            let index = 0;

            /* Prepare the form data to send chunks */
            let form = new FormData();
            form.set('uuid', file.altum.uuid);
            form.set('chunk_index', index);
            form.set('total_chunks', total_chunks);
            form.set('total_file_size', file.size);
            form.set('file_name', file.name);
            for (let [key, value] of Object.entries(this.options.upload_file_endpoint_params)) {
                form.set(key, value);
            }

            /* Send request to server */
            let response = await fetch(this.options.upload_file_endpoint_url, {
                method: 'post',
                body: form
            });

            let data = null;
            try {
                data = await response.json();
            } catch (error) { /* :) */ }

            if (!response.ok) {
                /* Reset progress */
                file.altum.uploaded_size = 0;
                file.altum.upload_progress = 0;

                /* Emit event */
                this.emit('upload_file_error', {file, response_data: data});
                return false;
            }

            let file_name = data.data.file_name;
            let offload_id = data.data.offload_id;
            let upload_urls = data.data.upload_urls;
            let url_index = 0;

            /* Prepare the parts - responses from external */
            let uploaded_chunks = [];

            /* Start to upload */
            for (let chunk_offset = 0; chunk_offset < file.size; chunk_offset += chunk_size_limit) {
                /* Make sure the file was not deleted */
                if (this.files[file.altum.uuid] === undefined) {
                    return false;
                }

                /* Prepare to send chunks */
                const chunk = file.slice(chunk_offset, chunk_offset + chunk_size_limit);
                const chunk_size = chunk.size;

                /* Upload progress */
                this.files[file.altum.uuid].uploaded_size = file.altum.uploaded_size += chunk_size;
                this.files[file.altum.uuid].upload_progress = file.altum.upload_progress = (file.altum.uploaded_size * 100 / file.size).toFixed(2);
                if (file.altum.upload_progress > 100) this.files[file.altum.uuid].upload_progress = file.altum.upload_progress = 100;

                /* Update total stats */
                this.extra.total_uploaded_size += chunk_size;
                this.extra.total_upload_progress = (this.extra.total_uploaded_size * 100 / this.extra.total_size).toFixed(2);

                /* Emit event */
                this.emit('uploading_file', file);

                /* Send request to server */
                let response = await fetch(upload_urls[url_index], {
                    method: 'PUT',
                    body: chunk
                });

                if (!response.ok) {
                    /* Reset progress */
                    file.altum.uploaded_size = 0;
                    file.altum.upload_progress = 0;

                    /* Emit event */
                    this.emit('upload_file_error', {file, response_data: data});
                    return false;
                }

                uploaded_chunks.push({
                    PartNumber: url_index + 1,
                    ETag: response.headers.get('etag')
                });

                /* Emit event */
                this.emit('upload_file_success', {file, response_data: data});

                index++;
                url_index++;
            }

            /* Prepare the form data to send chunks */
            form = new FormData();
            form.set('offload_id', offload_id);
            form.set('uploaded_chunks', JSON.stringify(uploaded_chunks));
            form.set('file_name', file_name);
            form.set('uuid', file.altum.uuid);
            for (let [key, value] of Object.entries(this.options.upload_file_endpoint_params)) {
                form.set(key, value);
            }

            /* Send request to server */
            response = await fetch(this.options.upload_file_endpoint_url, {
                method: 'post',
                body: form
            });

            data = null;
            try {
                data = await response.json();
            } catch (error) { /* :) */ }


            return true;
        }

        /* Through server uploading */
        else {

            const total_chunks = Math.ceil(file.size / this.options.chunk_size_limit);
            let index = 0;

            for (let chunk_offset = 0; chunk_offset < file.size; chunk_offset += this.options.chunk_size_limit) {

                /* Make sure the file was not deleted */
                if (this.files[file.altum.uuid] === undefined) {
                    return false;
                }

                /* Prepare the form data to send chunks */
                const form = new FormData();

                const chunk = file.slice(chunk_offset, chunk_offset + this.options.chunk_size_limit);
                const chunk_size = chunk.size;
                form.set('file', chunk);

                form.set('uuid', file.altum.uuid);
                form.set('chunk_index', index);
                form.set('total_chunks', total_chunks);
                form.set('total_file_size', file.size);
                form.set('file_name', file.name);
                for (let [key, value] of Object.entries(this.options.upload_file_endpoint_params)) {
                    form.set(key, value);
                }

                /* Upload progress */
                this.files[file.altum.uuid].uploaded_size = file.altum.uploaded_size += chunk_size;
                this.files[file.altum.uuid].upload_progress = file.altum.upload_progress = (file.altum.uploaded_size * 100 / file.size).toFixed(2);
                if (file.altum.upload_progress > 100) this.files[file.altum.uuid].upload_progress = file.altum.upload_progress = 100;

                /* Update total stats */
                this.extra.total_uploaded_size += chunk_size;
                this.extra.total_upload_progress = (this.extra.total_uploaded_size * 100 / this.extra.total_size).toFixed(2);

                /* Emit event */
                this.emit('uploading_file', file);

                /* Send request to server */
                let response = await fetch(this.options.upload_file_endpoint_url, {
                    method: 'post',
                    body: form
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (error) { /* :) */ }

                if (!response.ok) {
                    /* Reset progress */
                    file.altum.uploaded_size = 0;
                    file.altum.upload_progress = 0;

                    /* Emit event */
                    this.emit('upload_file_error', {file, response_data: data});
                    return false;
                }

                /* Emit event */
                this.emit('upload_file_success', {file, response_data: data});

                index++;
            }

        }

        return true;
    }

    /* Events handler */
    on(name, callback) {
        if(!this.events[name]) {
            throw new Error(`Can't add ${name} event as it is not defined in the events array.`);
        }

        this.events[name].push(callback);
    }

    emit(name, data) {
        if(!this.events[name]) {
            throw new Error(`Can't emit ${name} event as it is not defined in the events array.`);
        }

        this.events[name].forEach(callback => callback(data));
    }

    /* Helpers */
    alert_when_closing_window(event) {
        event.preventDefault();
        event.returnValue = '';
    }

    generate_uuid() {
        return crypto.randomUUID().replace(/-/g, "");
    }

    get_formatted_bytes(bytes) {
        let selected_size = 0;
        let selected_unit = 'B';

        if(bytes > 0) {
            let units = ['TB', 'GB', 'MB', 'KB', 'B'];

            for (let i = 0; i < units.length; i++) {
                let unit = units[i];
                let cutoff = Math.pow(1000, 4 - i) / 10;

                if(bytes >= cutoff) {
                    selected_size = bytes / Math.pow(1000, 4 - i);
                    selected_unit = unit;
                    break;
                }
            }

            selected_size = Math.round(10 * selected_size) / 10;
        }

        return `${selected_size} ${selected_unit}`;
    }
}
