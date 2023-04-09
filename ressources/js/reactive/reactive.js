export {applyAndRegister, reactive, startReactiveDom};

let objectByName = new Map();
let registeringEffect = null;
let objetDependencies = new Map();

function applyAndRegister(effect) {
    registeringEffect = effect;
    effect();
    registeringEffect = null;
}

function trigger(target, key) {
    if (!objetDependencies.get(target).has(key)) return;
    for (let effect of objetDependencies.get(target).get(key)) effect();
}

function reactive(passiveObject, name) {
    objetDependencies.set(passiveObject, new Map());
    const handler = {
        get(target, key) {
            if (registeringEffect !== null)
                registerEffect(target, key);
            return target[key];
        },
        set(target, key, value) {
            target[key] = value;
            trigger(target, key);
            return true;
        },
    };

    const reactiveObject = new Proxy(passiveObject, handler);
    objectByName.set(name, reactiveObject);
    return reactiveObject;
}

function startReactiveDom(doc = document) {
    for (let elementClickable of doc.querySelectorAll("[data-onclick]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            objectByName.get(nomObjet)[methode](argument);
        });
    }

    for (let elementClickable of doc.querySelectorAll("[data-htmlfun]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.htmlfun.split(/[.()]+/);
        applyAndRegister(() => {
            elementClickable.innerHTML = objectByName.get(nomObjet)[methode](argument);
            startReactiveDom(elementClickable)
        })
    }

    // for (let rel of document.querySelectorAll("[data-textfun]")) {
    //     const [obj, fun, arg] = rel.dataset.textfun.split(/[.()]+/);
    //     applyAndRegister(() => {
    //         rel.textContent = objectByName.get(obj)[fun](arg)
    //     });
    // }

    // for (let rel of document.querySelectorAll("[data-textvar]")) {
    //     const [obj, prop] = rel.dataset.textvar.split('.');
    //     applyAndRegister(() => {
    //         rel.textContent = objectByName.get(obj)[prop]
    //     });
    // }

    // for (let rel of document.querySelectorAll("[data-stylefun]")) {
    //     const [obj, fun, arg] = rel.dataset.stylefun.split(/[.()]+/);
    //     applyAndRegister(() => {Object.assign(rel.style, objectByName.get(obj)[fun](arg))});
    // }
}

function registerEffect(target, key) {
    if (objetDependencies.get(target).has(key)) objetDependencies.get(target).get(key).add(registeringEffect);
    else objetDependencies.get(target).set(key, new Set());
}
