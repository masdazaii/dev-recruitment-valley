import $ from "jquery";

let store = {
	isFirebaseInitialized: false,
	isLoadingScreenImg: true,
	forceAppLoading: false,
	forceHideAppLoading: false,
};

/**
 * @param {string} name key of store property that you want to set
 * @param {string} errorMethod method name to be shown in the error message (if the name is not exist)
 * @returns {boolean}
 */
const checkKeyExistence = (name, errorMethod) => {
	const isKeyExist = Object.keys(store).includes(name);
	if (!isKeyExist) {
		console.error(`Trying to ${errorMethod} non-existing key of '${name}'`);
		return false;
	}

	return true;
};

/**
 * @template {keyof store} T
 * @param {T} name key of store property that you want to set
 * @param {typeof store[T]} newValue
 * @returns {typeof store | undefined} updated value of entire store
 */
export const setStoreValue = (name, newValue) => {
	const isExist = checkKeyExistence(name, "set");
	if (!isExist) return;

	store[name] = newValue;
	$(document).trigger(`bd-store:${name}`, store);
	return store;
};

/**
 * Get a particual property from the store
 *
 * @param {keyof store} name key of store property that you want to get
 * @returns {any} store value property
 */
export const getStoreValue = (name) => {
	const isExist = checkKeyExistence(name, "get");
	if (!isExist) return;
	return store[name];
};

/**
 * Get entire store value
 * @returns {typeof store}
 */
export const getStore = () => store;

/**
 * Subscribe to property changes
 * @param {(currentStore: typeof store) => {}} callback
 * @param {keyof store | Array<keyof store>} property
 * @returns {() => {}} cleanup function
 */
export const subscribe = (callback, property) => {
	const isArray = Array.isArray(property);
	const properties = isArray ? property : [property];
	const events = properties.map((property) => `bd-store:${property}`).join(" ");
	const localCallback = (e, store) => callback(store);

	$(document).on(events, localCallback);
	return () => $(document).off(events, localCallback);
};
