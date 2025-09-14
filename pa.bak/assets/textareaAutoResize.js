const textareaTypes = new Set([
    'APL', 'CERT', 'CDNSKEY', 'CDS', 'CSYNC', 'DHCID', 'DLV', 'DNSKEY', 'DS',
    'HTTPS', 'IPSECKEY', 'KEY', 'LUA', 'NAPTR', 'NSEC', 'NSEC3', 'NSEC3PARAM',
    'OPENPGPKEY', 'RKEY', 'RRSIG', 'SIG', 'SMIMEA', 'SPF', 'SSHFP', 'SVCB',
    'TLSA', 'TKEY', 'TSIG', 'TXT', 'URI', 'ZONEMD'
]);

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function updateContentInput(selectId, containerId, contentId) {
    const elements = {
        select: document.getElementById(selectId),
        container: document.getElementById(containerId)
    };

    if (!elements.select || !elements.container) return;

    const currentInput = document.getElementById(contentId);
    const currentClasses = currentInput ? currentInput.classList.toString() : '';
    const currentValue = currentInput ? currentInput.value : '';
    const currentName = currentInput ? currentInput.name : 'content';
    const isTextarea = textareaTypes.has(elements.select.value);

    const escapedValue = escapeHtml(currentValue);

    elements.container.innerHTML = isTextarea
        ? `<textarea id="${contentId}" class="${currentClasses}" name="${currentName}" rows="1" required>${escapedValue}</textarea>`
        : `<input id="${contentId}" class="${currentClasses}" type="text" name="${currentName}" value="${escapedValue}" data-testid="record-content-input" required>`;

    if (isTextarea) {
        const textarea = document.getElementById(contentId);
        const adjustHeight = () => {
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight}px`;
        };

        textarea.removeEventListener('input', adjustHeight);
        textarea.addEventListener('input', adjustHeight);
        if (textarea.value) adjustHeight();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('[id^="contentInputContainer"]');
    containers.forEach(container => {
        const baseString = "contentInputContainer";
        const suffix = container.id.slice(baseString.length);

        const selectId = `recordTypeSelect${suffix}`;
        const contentId = `recordContent${suffix}`;
        const select = document.getElementById(selectId);

        if (container && select) {
            container.dataset.initialValue = document.getElementById(contentId).value;
            updateContentInput(selectId, container.id, contentId);
            select.addEventListener('change', () => updateContentInput(selectId, container.id, contentId));
        }
    });
});
