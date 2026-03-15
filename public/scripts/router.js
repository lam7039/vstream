import { ajax_get, ajax_post } from "./ajax";

class Router {
    constructor() {
        this.routes = [];
    }

    create = (path, handler) => {
        this.routes.push({ path, handler });
    };

    resolve = (url) => {
        for (const route of this.routes) {
            if (url === route.path) {
                return route;
            }
        }
        return null;
    };

    navigate = (url) => {
        const current = resolve(url).handler;
        //TODO: request php templating
    }
}

const router = new Router;

export function route_add(path, handler) {
    router.create(path, handler);
}

export function route_get(path) {
    router.resolve(path);
}

export function navigate(url) {
    router.navigate(url);
}

export default Router;
