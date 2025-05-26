import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp(require.context(
    './controllers',
    true,
    /\.(js|ts)$/
));
