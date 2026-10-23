
document.addEventListener('DOMContentLoaded', function() {
    fetch('/wp-json/ds/v1/my-profile', {credentials:'include'})
    .then(function(r){ return r.json(); })
    .then(function(u){
        if (!u || u.code) return;
        var nameEl  = document.getElementById('ds-cust-name');
        var emailEl = document.getElementById('ds-cust-email');
        if (nameEl && u.name) {
            nameEl.value = u.name;
            nameEl.setAttribute('readonly', 'readonly');
            nameEl.setAttribute('autocomplete', 'off');
            nameEl.style.cssText += 'opacity:0.6;cursor:not-allowed;pointer-events:none;-webkit-text-fill-color:#ffffff !important;';
        }
        if (emailEl && u.email) {
            emailEl.value = u.email;
            emailEl.setAttribute('readonly', 'readonly');
            emailEl.setAttribute('autocomplete', 'off');
            emailEl.style.cssText += 'opacity:0.6;cursor:not-allowed;pointer-events:none;-webkit-text-fill-color:#ffffff !important;';
        }
    })
    .catch(function(err){ console.log('Profile fetch error:', err); });
});

function dsShowFiles(input) {
    var list = document.getElementById('ds-files-list');
    if (input.files.length > 0) {
        var names = Array.from(input.files).map(function(f) {
            return '📎 ' + f.name;
        }).join('<br>');
        list.innerHTML = names;
    }
}

function dsSubmitCustomForm(btn) {
    var name  = document.querySelector('.ds-cust-wrap input[type="text"]').value.trim();
    var email = document.querySelector('.ds-cust-wrap input[type="email"]').value.trim();
    var desc  = document.querySelector('.ds-cust-wrap textarea').value.trim();
    var selects = document.querySelectorAll('.ds-cust-wrap .ds-cust-select');
    var designType = selects[0] ? selects[0].value : '';
    var budget = selects[1] ? selects[1].value : '';
    var deadline = document.querySelector('.ds-cust-wrap .ds-cust-date').value;
    var driveLink = document.getElementById('ds-cust-drive').value.trim();
    var fileInput = document.getElementById('ds-file-input');
    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
    if (!name || !email || !desc || !designType || !budget || !deadline) {
        alert('Please fill in all required fields.');
        return;
    }
    var oversized = false;
    if (hasFile) {
        Array.from(fileInput.files).forEach(function(f){ if (f.size > 100 * 1024 * 1024) oversized = true; });
    }
    if (oversized) {
        alert('Each file must be 100MB or smaller.');
        return;
    }
    if (!hasFile && !driveLink) {
        alert('Please upload a reference file or paste a Google Drive link.');
        return;
    }
    dsSubmitActualForm();
}
function dsSubmitActualForm() {
    var btn = document.querySelector('.ds-cust-submit');
    btn.disabled = true;
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="animation:dsSpin 0.8s linear infinite"><circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="2"/><path d="M12 2a10 10 0 0110 10" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg> Sending...';

    var name  = document.querySelector('.ds-cust-wrap input[type="text"]').value.trim();
    var email = document.querySelector('.ds-cust-wrap input[type="email"]').value.trim();
    var desc  = document.querySelector('.ds-cust-wrap textarea').value.trim();

    var formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('description', desc);

    var selects = document.querySelectorAll('.ds-cust-wrap .ds-cust-select');
    formData.append('design_type', selects[0] ? selects[0].value : '');
    formData.append('budget',      selects[1] ? selects[1].value : '');
    var deadlineVal = document.querySelector('.ds-cust-wrap .ds-cust-date');
    formData.append('deadline', deadlineVal ? deadlineVal.value : '');
	var driveLink = document.getElementById('ds-cust-drive');
formData.append('drive_link', driveLink ? driveLink.value : '');

    var fileInput = document.getElementById('ds-file-input');
    if (fileInput && fileInput.files.length > 0) {
        Array.from(fileInput.files).forEach(function(file) {
            formData.append('files[]', file);
        });
    }

    fetch('/wp-json/ds/v1/submit-design-request', { method:'POST', body:formData, credentials:'include' })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.success) {
            var toast = document.createElement('div');
            toast.innerHTML = '🎉 Thank you for submitting your request!<br><span style="font-size:13px;font-weight:400;opacity:0.85;">We will contact you soon.</span>';
            toast.style.cssText = 'position:fixed;top:32px;left:50%;transform:translateX(-50%) translateY(-20px);background:#10B981;color:#fff;padding:18px 32px;border-radius:14px;font-size:16px;font-weight:700;text-align:center;z-index:99999;box-shadow:0 8px 32px rgba(16,185,129,0.35);opacity:0;transition:all 0.4s ease;line-height:1.6;';
            document.body.appendChild(toast);
            setTimeout(function(){ toast.style.opacity='1'; toast.style.transform='translateX(-50%) translateY(0)'; }, 10);
            setTimeout(function(){ toast.style.opacity='0'; toast.style.transform='translateX(-50%) translateY(-20px)'; setTimeout(function(){ toast.remove(); }, 400); }, 3500);
            btn.disabled = false;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Send My Request';
            btn.style.background = '#E8442A';
            document.querySelector('.ds-cust-wrap input[type="text"]').value = '';
            document.querySelector('.ds-cust-wrap input[type="email"]').value = '';
            document.querySelector('.ds-cust-wrap textarea').value = '';
            document.querySelector('.ds-cust-wrap input[type="date"]').value = '';
            document.getElementById('ds-file-input').value = '';
            document.getElementById('ds-files-list').innerHTML = '';
            document.querySelectorAll('.ds-cust-select').forEach(function(s){ s.selectedIndex = 0; });
        } else {
            btn.disabled = false;
            btn.innerHTML = '⚠️ Failed. Try again.';
            setTimeout(function(){
                btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Send My Request';
                btn.style.background = '#E8442A';
                btn.disabled = false;
            }, 2000);
        }
    })
    .catch(function(){
        btn.disabled = false;
        btn.innerHTML = 'Send My Request';
    });
}

