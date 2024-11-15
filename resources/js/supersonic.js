import Supersonic from './components/supersonic.vue';
import SupersonicActionList from './components/supersonic-action-list.vue';
import SupersonicKeyListener from './components/supersonic-key-listener.vue';
import SupersonicActionLibrary from "./action-library";

// Register our component with Statamic.
Statamic.booting(() => {
    Statamic.$components.register('supersonic-app', Supersonic);
    Statamic.$components.register('supersonic-action-list', SupersonicActionList);
    Statamic.$components.register('supersonic-key-listener', SupersonicKeyListener);
});

// Instantiate our helpers.
const actionLibrary = new SupersonicActionLibrary();

// Initialise the component.
async function supersonicInitialise() {
    await actionLibrary.init();
    const component = Statamic.$components.append('supersonic-app', {
        props: {
            actions: actionLibrary.get(),
            isVisible: false,
            mode: 'main'
        }
    });
}
if (document.readyState !== 'loading') {
    supersonicInitialise();
} else {
    document.addEventListener('DOMContentLoaded', supersonicInitialise);
}


