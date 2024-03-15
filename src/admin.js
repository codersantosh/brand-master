/* *********===================== Setup store ======================********* */
import { AtrcApis, AtrcStore, AtrcRegisterStore } from 'atrc/build/data';

AtrcApis.baseUrl({
	key: 'atrc-global-api-base-url',
	// eslint-disable-next-line no-undef
	url: brandMasterLocalize.rest_url,
});

/* Settings */
AtrcApis.register({
	key: 'settings',
	path: 'brand-master/v1/settings',
	type: 'settings',
});

/* Settings Local for user preferance */
AtrcStore.register({
	key: 'brandMasterLocal',
	type: 'localStorage',
});

// eslint-disable-next-line no-undef
AtrcApis.xWpNonce(brandMasterLocalize.nonce);
window.atrcStore = AtrcRegisterStore(brandMasterLocalize.store);

import './admin/routes';
