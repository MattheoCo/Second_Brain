async function apiLoad(ns){
    const r = await fetch(`/api/userdata/${encodeURIComponent(ns)}`, { credentials: 'same-origin' });
    if(!r.ok) throw new Error('Load failed');
    return (await r.json()).state;
}
async function apiSave(ns, state){
    const r = await fetch(`/api/userdata/${encodeURIComponent(ns)}`, {
        method: 'PUT',
        headers: { 'Content-Type':'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ state })
    });
    if(!r.ok){
        const text = await r.text().catch(()=> '');
        throw new Error('Save failed: ' + (text || r.status));
    }
}
