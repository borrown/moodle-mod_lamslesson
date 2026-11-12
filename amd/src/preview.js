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
 * JavaScript for the LAMS lesson form: sequence tree and preview/author buttons.
 *
 * @module     mod_lamslesson/preview
 * @copyright  2011 LAMS Foundation - Ernie Ghiglione
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Tree from 'core/tree';

const SELECTORS = {
    tree: '#treeDiv',
    treeList: '#treeDiv ul[role="tree"]',
    sequence: 'input[name="sequence_id"]',
    name: 'input[name="name"]',
    previewButton: '#previewbutton',
    authorButton: '#authorbutton',
    refreshButton: '#refreshbutton',
};

/**
 * Centre a popup window on the current screen.
 *
 * @param {String} url The URL to open.
 * @param {String} title The window name.
 * @param {Number} width The popup width.
 * @param {Number} height The popup height.
 */
const popupCenter = (url, title, width, height) => {
    const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
    const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
    const viewportWidth = window.innerWidth ? window.innerWidth :
        document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    const viewportHeight = window.innerHeight ? window.innerHeight :
        document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
    const left = ((viewportWidth / 2) - (width / 2)) + dualScreenLeft;
    const top = ((viewportHeight / 2) - (height / 2)) + dualScreenTop;
    const newWindow = window.open(
        url,
        title,
        `scrollbars=yes, width=${width}, height=${height}, top=${top}, left=${left}`
    );

    if (newWindow && window.focus) {
        newWindow.focus();
    }
};

/**
 * Show or hide the preview button.
 *
 * @param {Boolean} visible Whether the button should be visible.
 */
const setPreviewVisibility = (visible) => {
    const button = document.querySelector(SELECTORS.previewButton);
    if (button) {
        button.style.visibility = visible ? 'visible' : 'hidden';
    }
};

/**
 * Store the selected sequence in the form.
 *
 * @param {String} id The selected sequence id.
 * @param {String} name The selected sequence name.
 */
const selectSequence = (id, name) => {
    const sequenceInput = document.querySelector(SELECTORS.sequence);
    if (!sequenceInput) {
        return;
    }

    sequenceInput.value = id;

    const isSequence = id !== undefined && id !== null && id !== '' && id !== '0';
    if (isSequence) {
        const nameInput = document.querySelector(SELECTORS.name);
        if (nameInput && nameInput.value === '') {
            nameInput.value = name;
        }
        setPreviewVisibility(true);
    } else {
        setPreviewVisibility(false);
    }
};

/**
 * Build a tree item from a sequence/folder node.
 *
 * @param {Object} node The node data.
 * @returns {HTMLElement} The list item.
 */
const renderNode = (node) => {
    const item = document.createElement('li');
    item.setAttribute('role', 'treeitem');
    item.dataset.id = node.id ?? 0;
    item.dataset.label = node.label ?? '';

    const label = document.createElement('p');
    label.textContent = node.label ?? '';
    item.appendChild(label);

    if (Array.isArray(node.children) && node.children.length > 0) {
        const expanded = node.expanded !== false;
        item.setAttribute('aria-expanded', expanded ? 'true' : 'false');

        const group = document.createElement('ul');
        group.setAttribute('role', 'group');
        if (!expanded) {
            group.setAttribute('aria-hidden', 'true');
        }

        node.children.forEach((child) => group.appendChild(renderNode(child)));
        item.appendChild(group);
    }

    return item;
};

/**
 * Render the whole tree into its container.
 *
 * @param {HTMLElement} container The tree container.
 * @param {Array} nodes The tree nodes.
 */
const renderTree = (container, nodes) => {
    const list = document.createElement('ul');
    list.className = 'foldertree';
    list.setAttribute('role', 'tree');
    nodes.forEach((node) => list.appendChild(renderNode(node)));
    container.appendChild(list);
};

/**
 * Open the learning design preview window.
 *
 * @param {Object} config The configuration from the tree container.
 */
const openPreview = (config) => {
    const sequenceInput = document.querySelector(SELECTORS.sequence);
    const sequenceId = sequenceInput ? sequenceInput.value : '';
    const url = config.previewUrl +
        '&ldId=' + encodeURIComponent(sequenceId) +
        '&course=' + encodeURIComponent(config.course);

    popupCenter(url, 'preview', 1280, 720);
};

/**
 * Initialise the form JavaScript.
 */
export const init = () => {
    const container = document.querySelector(SELECTORS.tree);
    if (!container) {
        return;
    }

    let sequences = [];
    try {
        sequences = JSON.parse(container.dataset.sequences || '[]');
    } catch (error) {
        sequences = [];
    }

    renderTree(container, sequences);

    const config = {
        course: container.dataset.course,
        previewUrl: container.dataset.previewUrl,
    };

    const sequenceInput = document.querySelector(SELECTORS.sequence);
    setPreviewVisibility(sequenceInput && sequenceInput.value !== '' && sequenceInput.value !== '0');

    if (container.querySelector('ul[role="tree"]')) {
        new Tree(SELECTORS.treeList, (item) => {
            const node = item[0];
            const id = node ? node.dataset.id : '';
            if (id !== undefined && id !== '' && id !== '0') {
                selectSequence(id, node.dataset.label);
            }
        });
    }

    const previewButton = document.querySelector(SELECTORS.previewButton);
    if (previewButton) {
        previewButton.addEventListener('click', (event) => {
            event.preventDefault();
            openPreview(config);
        });
    }

    const authorButton = document.querySelector(SELECTORS.authorButton);
    if (authorButton) {
        authorButton.addEventListener('click', (event) => {
            event.preventDefault();
            popupCenter(authorButton.dataset.url, 'author', 1280, 720);
        });
    }

    const refreshButton = document.querySelector(SELECTORS.refreshButton);
    if (refreshButton) {
        refreshButton.addEventListener('click', (event) => {
            event.preventDefault();
            window.location.reload();
        });
    }
};
