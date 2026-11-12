// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * LAMS lesson settings validation and server time offset helpers.
 *
 * @module     mod_lamslesson/validate
 * @copyright  2011 LAMS Foundation - Ernie Ghiglione
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Config from 'core/config';
import {get_string as getString} from 'core/str';

const SELECTORS = {
    serverUrl: '#id_s__lamslesson_serverurl',
    serverId: '#id_s__lamslesson_serverid',
    serverKey: '#id_s__lamslesson_serverkey',
    offsetButton: '#offsetbtn',
    validateButton: '#validatebtn',
};

/**
 * Get the value of a form field.
 *
 * @param {String} selector The field selector.
 * @returns {String}
 */
const getValue = (selector) => {
    const field = document.querySelector(selector);
    return field ? field.value.trim() : '';
};

/**
 * Build the settings query sent to the LAMS helper endpoints.
 *
 * @returns {URLSearchParams}
 */
const createQuery = () => {
    const params = new URLSearchParams();
    params.append('u', getValue(SELECTORS.serverUrl));
    params.append('i', getValue(SELECTORS.serverId));
    params.append('k', getValue(SELECTORS.serverKey));
    return params;
};

/**
 * POST a request to one of the LAMS helper endpoints.
 *
 * @param {String} endpoint The endpoint filename.
 * @param {URLSearchParams} params The request parameters.
 * @returns {Promise<String>}
 */
const post = async (endpoint, params) => {
    const response = await fetch(`${Config.wwwroot}/mod/lamslesson/${endpoint}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: params.toString(),
    });

    const result = await response.text();
    if (!response.ok) {
        throw new Error(result);
    }

    return result;
};

/**
 * Mark a button as busy while a request is running.
 *
 * @param {HTMLElement} button The button.
 * @param {Boolean} busy Whether the button is busy.
 */
const setButtonBusy = (button, busy) => {
    if (!button) {
        return;
    }

    if (busy) {
        button.dataset.originalText = button.textContent;
        button.textContent = button.textContent + '...';
        button.classList.add('disabled');
        button.setAttribute('aria-disabled', 'true');
    } else {
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
        button.classList.remove('disabled');
        button.removeAttribute('aria-disabled');
    }
};

/**
 * Validate that the LAMS server settings are complete.
 *
 * @returns {Promise<Boolean>}
 */
const validateFields = async () => {
    const [required, invalidUrl] = await Promise.all([
        getString('required', 'core'),
        getString('invalidserverurl', 'mod_lamslesson'),
    ]);

    const serverUrl = getValue(SELECTORS.serverUrl);
    const serverId = getValue(SELECTORS.serverId);
    const serverKey = getValue(SELECTORS.serverKey);

    let errors = '';
    let firstInvalidField = null;
    const serverUrlField = document.querySelector(SELECTORS.serverUrl);
    const serverIdField = document.querySelector(SELECTORS.serverId);
    const serverKeyField = document.querySelector(SELECTORS.serverKey);

    if (serverUrl.length === 0) {
        errors += `* server_url ${required}\n`;
        firstInvalidField = serverUrlField;
    } else if (!/^https?:\/\//i.test(serverUrl)) {
        errors += `* server_url ${invalidUrl}\n`;
        firstInvalidField = serverUrlField;
    }

    if (serverId.length === 0) {
        errors += `* server_id ${required}\n`;
        firstInvalidField = firstInvalidField || serverIdField;
    }

    if (serverKey.length === 0) {
        errors += `* server_key ${required}\n`;
        firstInvalidField = firstInvalidField || serverKeyField;
    }

    if (errors.length > 0) {
        window.alert(errors.trim());
        if (firstInvalidField) {
            firstInvalidField.focus();
        }
        return false;
    }

    return true;
};

/**
 * Verify the settings against the LAMS server.
 */
const verify = async () => {
    const button = document.querySelector(SELECTORS.validateButton);
    setButtonBusy(button, true);
    try {
        const result = await post('verify.php', createQuery());
        if (result.trim() !== '1') {
            window.alert(result);
        }
    } catch (error) {
        window.alert(error.message);
    } finally {
        setButtonBusy(button, false);
    }
};

/**
 * Validate the settings and verify them with the LAMS server.
 */
const validate = async () => {
    if (await validateFields()) {
        await verify();
    }
};

/**
 * Calculate and display the time offset between Moodle and the LAMS server.
 */
const calculateOffset = async () => {
    const button = document.querySelector(SELECTORS.offsetButton);
    setButtonBusy(button, true);
    try {
        const result = await post('offset.php', createQuery());
        if (result.trim() !== '1') {
            window.alert(result);
        }
    } catch (error) {
        window.alert(error.message);
    } finally {
        setButtonBusy(button, false);
    }
};

/**
 * Initialise the settings page JavaScript.
 */
export const init = () => {
    const offsetButton = document.querySelector(SELECTORS.offsetButton);
    if (offsetButton) {
        offsetButton.addEventListener('click', (event) => {
            event.preventDefault();
            calculateOffset();
        });
    }

    const validateButton = document.querySelector(SELECTORS.validateButton);
    if (validateButton) {
        validateButton.addEventListener('click', (event) => {
            event.preventDefault();
            validate();
        });
    }
};
