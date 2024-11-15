export default class SupersonicActionLibrary {
    #data;
    async init() {
        const json = await fetch('/cp/!/ademti-apps/supersonic/actions')
            .then((response) => {
                if (!response.ok) {
                    return {};
                }
                return response.json();
            }).catch(() => {
                return {};
            });
        this.data = await json;
    }

    get() {
        return this.data;
    }
}
