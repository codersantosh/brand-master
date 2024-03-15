/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';

/* Library */
import { isEmpty } from 'lodash';

/*Atrc*/
import {
	AtrcRoute,
	AtrcRoutes,
	AtrcNavigate,
	AtrcNav,
	AtrcWireFrameSidebarContent,
} from 'atrc';

/*Inbuilt*/
import { Advanced } from './pages';
import { AtrcReduxContextData } from '../../routes';
import { SaveSettings } from '../../components/atoms';

/*Local*/
const SettingsRouters = () => {
	return (
		<>
			<AtrcRoutes>
				<AtrcRoute
					exact
					path='advanced/*'
					element={<Advanced />}
				/>
				<AtrcRoute
					path='/'
					element={
						<AtrcNavigate
							to='advanced'
							replace
						/>
					}
				/>
			</AtrcRoutes>
			<SaveSettings />
		</>
	);
};

const InitSettings = () => {
	const data = useContext(AtrcReduxContextData);
	const { dbSettings } = data;

	if (isEmpty(dbSettings)) {
		return null;
	}
	return (
		<AtrcWireFrameSidebarContent
			wrapProps={{
				tag: 'div',
				className: 'at-ctnr-fld',
			}}
			rowProps={{}}
			renderSidebar={
				<AtrcNav
					variant='vertical'
					navs={[
						{
							to: 'advanced',
							children: __('Advanced', 'brand-master'),
						},
					]}
				/>
			}
			renderContent={<SettingsRouters />}
			contentProps={{
				tag: 'div',
				contentCol: 'at-col-10',
			}}
			sidebarProps={{
				sidebarCol: 'at-col-2',
			}}
		/>
	);
};

export default InitSettings;
