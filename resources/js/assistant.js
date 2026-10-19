const STORAGE_KEY = 'iomp-assistant-conversation';

function readConversation() {
    try {
        return window.sessionStorage.getItem(STORAGE_KEY) || '';
    } catch {
        return '';
    }
}

function writeConversation(value) {
    try {
        if (value) {
            window.sessionStorage.setItem(STORAGE_KEY, value);
        }
    } catch {
        // A private browser may refuse storage. The reply still renders.
    }
}

function initAssistant() {
    const root = document.querySelector('[data-assistant]');

    if (!root || root.dataset.ready === 'yes') {
        return;
    }

    root.dataset.ready = 'yes';

    const endpoint = root.dataset.endpoint;
    const panel = root.querySelector('[data-assistant-panel]');
    const thread = root.querySelector('[data-assistant-thread]');
    const form = root.querySelector('[data-assistant-form]');
    const input = root.querySelector('[data-assistant-input]');
    const toggles = root.querySelectorAll('[data-assistant-toggle]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    let conversation = readConversation();
    let busy = false;

    function setOpen(open) {
        panel.hidden = !open;
        root.classList.toggle('is-open', open);
        toggles.forEach((button) => button.setAttribute('aria-expanded', String(open)));

        if (open) {
            window.setTimeout(() => input.focus(), 40);
        }
    }

    toggles.forEach((button) => button.addEventListener('click', () => setOpen(panel.hidden)));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            setOpen(false);
        }
    });

    root.querySelectorAll('[data-assistant-suggestion]').forEach((button) => {
        button.addEventListener('click', () => {
            input.value = button.textContent.trim();
            form.requestSubmit();
        });
    });

    function append(role, text, citations = []) {
        const item = document.createElement('div');
        item.className = `assistant-message assistant-message-${role}`;
        const paragraph = document.createElement('p');
        paragraph.textContent = text;
        item.append(paragraph);

        if (citations.length > 0) {
            const list = document.createElement('ul');
            list.className = 'assistant-sources';
            citations.forEach((citation) => {
                const entry = document.createElement('li');
                entry.textContent = citation.label;
                list.append(entry);
            });
            item.append(list);
        }

        thread.append(item);
        thread.scrollTop = thread.scrollHeight;

        return item;
    }

    function showTyping() {
        const item = document.createElement('div');
        item.className = 'assistant-typing';
        item.setAttribute('role', 'status');
        item.setAttribute('aria-label', 'The assistant is writing');

        for (let step = 0; step < 3; step += 1) {
            item.append(document.createElement('span'));
        }

        thread.append(item);
        thread.scrollTop = thread.scrollHeight;

        return item;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();

        if (!message || busy) {
            return;
        }

        append('user', message);
        input.value = '';
        busy = true;
        const pending = showTyping();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ message, conversation: conversation || null }),
            });

            pending.remove();

            if (response.status === 429) {
                append('bot', 'You are sending messages a little too quickly. Please wait a moment and try again.');
                return;
            }

            if (!response.ok) {
                append('bot', 'I could not answer just now. Please try again, or use the contact page.');
                return;
            }

            const payload = await response.json();
            conversation = payload.conversation || conversation;
            writeConversation(conversation);
            append('bot', payload.reply?.content || 'I could not answer just now.', payload.reply?.citations || []);
        } catch {
            pending.remove();
            append('bot', 'I could not reach the assistant. Check your connection, or use the contact page.');
        } finally {
            busy = false;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAssistant);
} else {
    initAssistant();
}
