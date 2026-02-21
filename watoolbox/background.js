var manifest = chrome.runtime.getManifest();
chrome.runtime.onInstalled.addListener(function (details) {
    if (details.reason == "install") {
        chrome.tabs.create({ url: 'https://watoolbox.com/changelog?v=' + manifest.version });
    }
    chrome.tabs.query({ url: "https://web.whatsapp.com/*" }, function (tabs) {
        if (tabs.length > 0) {
            chrome.tabs.reload(tabs[0].id);
        }
    });
});

chrome.action.onClicked.addListener(function (tab) {
    chrome.tabs.query({ url: "https://web.whatsapp.com/*", currentWindow: true }, function (tabs) {
        if (tabs.length > 0) {
            chrome.tabs.update(tabs[0].id, { highlighted: true, selected: true });
            chrome.tabs.sendMessage(tabs[0].id, { action: 'openDialog' });
        } else {
            chrome.tabs.create({ 'url': 'https://web.whatsapp.com' });
        }
    });
});

chrome.runtime.onMessage.addListener(function (request, sender, sendResponse) {
    console.log(request);
    if (request.action == 'getUserEmail') {
        chrome.identity.getProfileUserInfo(function (userinfo) {
            console.log(userinfo);
            sendResponse({ email: userinfo.email });
        });
        return true;
    } else if (request.action == 'getSettings') {
        chrome.storage.sync.get([request.key], function (result) {
            if (result && result[request.key]) {
                sendResponse(result[request.key]);
            } else {
                sendResponse(undefined);
            }
        });
        return true;
    } else if (request.action == 'setSettings') {
        chrome.storage.sync.set({ [request.key]: request.value }, function () {
            console.log(request);
            sendResponse({ success: true });
        });
        return true;
    } else if (request.action == 'unsetSettings') {
        chrome.storage.sync.remove([request.key], function () {
            console.log(request);
            sendResponse({ success: true });
        });
        return true;
    }
    return true;
});
