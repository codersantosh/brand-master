/* WordPress */
import { render, createContext, useContext, lazy, Suspense } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/* Library */
import { map, isEmpty } from 'lodash';

/*Atrc*/
import AtrcWrap from 'atrc/build/atoms/wrap';
import AtrcNotice from 'atrc/build/molecules/notice';
import AtrcWrapFloating from 'atrc/build/molecules/wrap-floating';
import AtrcMain from 'atrc/build/organisms/main';
import {
	HashRouter as AtrcHashRouter,
	Route as AtrcRoute,
	Routes as AtrcRoutes,
} from 'react-router-dom';

import { AtrcApplyWithSettings } from 'atrc/build/data';

/*Inbuilt*/
import AdminHeader from './components/organisms/admin-header';
const Initlanding = lazy( () =>
	import( /* webpackChunkName: "brand-master-landing" */ './pages/landing' )
);
const Login = lazy( () =>
	import( /* webpackChunkName: "brand-master-login" */ './pages/login/routes' )
);
const Dashboard = lazy( () =>
	import( /* webpackChunkName: "brand-master-dashboard" */ './pages/dashboard/routes' )
);
const Settings = lazy( () =>
	import( /* webpackChunkName: "brand-master-settings" */ './pages/settings/routes' )
);

/* Local */

/* ==============Create Local Storage and Database Settings Context================== */
export const AtrcReduxContextData = createContext();

/* Admin routes pages */
const AdminRoutes = () => {
	const data = useContext(AtrcReduxContextData);
	const { dbNotices, dbRemoveNotice } = data;

	return (
		<>
			<AdminHeader />
			<AtrcMain>
				<Suspense
					fallback={
						<div className='bm-loading' role='status'>
							{ __( 'Loading…', 'brand-master' ) }
						</div>
					}>
					<AtrcRoutes>
					<AtrcRoute
						index
						element={<Initlanding />}
					/>
					<AtrcRoute
						exact
						path='/login/*'
						element={<Login />}
					/>
					<AtrcRoute
						exact
						path='/dashboard/*'
						element={<Dashboard />}
					/>
					<AtrcRoute
						exact
						path='/settings/*'
						element={<Settings />}
					/>
				</AtrcRoutes>
				</Suspense>
				{/*Notice is common for settings*/}
				{!isEmpty(dbNotices) ? (
					<AtrcWrapFloating>
						{map(dbNotices, (value, key) => (
							<AtrcNotice
								key={key}
								autoDismiss={5000}
								onAutoRemove={() => dbRemoveNotice(key)}
								onRemove={() => dbRemoveNotice(key)}>
								{value.message}
							</AtrcNotice>
						))}
					</AtrcWrapFloating>
				) : null}
			</AtrcMain>
		</>
	);
};

/* Initilize database settings and add Context data*/
const InitDatabaseSettings = (props) => {
	const {
		isLoading,
		canSave,
		settings,
		updateSetting,
		saveSettings,
		notices,
		removeNotice,
		lsSettings,
		lsUpdateSetting,
		lsSaveSettings,
	} = props;

	const dbProps = {
		dbIsLoading: isLoading,
		dbCanSave: canSave,
		dbSettings: settings,
		dbUpdateSetting: updateSetting,
		dbSaveSettings: saveSettings,
		dbNotices: notices,
		dbRemoveNotice: removeNotice,
		lsSettings: lsSettings,
		lsUpdateSetting: lsUpdateSetting,
		lsSaveSettings: lsSaveSettings,
	};
	return (
		<AtrcReduxContextData.Provider value={{ ...dbProps }}>
			<AtrcHashRouter basename='/'>
				<AtrcWrap
					variant='wrp'
					className='at-box-szg at-m at-typ'>
					<AdminRoutes />
				</AtrcWrap>
			</AtrcHashRouter>
		</AtrcReduxContextData.Provider>
	);
};
const InitDataBaseSettingsWithHoc = AtrcApplyWithSettings(InitDatabaseSettings);

/* Initilize local storage */
const InitLocalStorageSettings = (props) => {
	const { settings, updateSetting, saveSettings } = props;
	const defaultSettings = {
		gs1: true /* getting started 1 */,
	};
	return (
		<InitDataBaseSettingsWithHoc
			atrcStore='brand-master'
			atrcStoreKey='settings'
			lsSettings={settings || defaultSettings}
			lsUpdateSetting={updateSetting}
			lsSaveSettings={saveSettings}
		/>
	);
};
const InitLocalStorageSettingsWithHoc = AtrcApplyWithSettings(
	InitLocalStorageSettings
);

/* Initilize react on oru div */
document.addEventListener('DOMContentLoaded', () => {
	// Check if the root element exists in the DOM
	const rootElement = document.getElementById(brandMasterLocalize.root_id);

	if (rootElement) {
		// Render the component into the root element
		render(
			<InitLocalStorageSettingsWithHoc
				atrcStore='brand-master'
				atrcStoreKey='brandMasterLocal'
			/>,
			rootElement
		);
	}
});
