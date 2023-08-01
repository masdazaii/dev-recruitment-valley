import "./polyfills";
import { setStoreValue, subscribe } from "@bd-stores";

subscribe((store) => {
	// eslint-disable-next-line no-console
	console.log("store changes", { store });
}, "isFirebaseInitialized");

setTimeout(() => {
	setStoreValue("isFirebaseInitialized", true);
}, 3000);

const testActionWorkflows = () => console.error("blablabla");
testActionWorkflows();
