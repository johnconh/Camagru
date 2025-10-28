function showMessage(message, type = 'info', containerId = 'message-container') {
    const container = document.getElementById(containerId);
    if (!container) return;

    const msgDiv = document.createElement('div');
    msgDiv.className = `alert ${type === 'error' ? 'alert-error' : 'alert-success'}`;
    msgDiv.innerText = `${type === 'error' ? '❌' : '✅'} ${message}`;

    container.innerHTML = '';
    container.appendChild(msgDiv);

    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => {
        if (container.contains(msgDiv)) {
            container.removeChild(msgDiv);
        }
    }, 5000);
}

window.showMessage = showMessage;