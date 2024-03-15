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
import { SaveSettings } from '../../components/atoms';
import { NoLogin, SidebarContent, HeaderContent, Elements } from './pages';
import { AtrcReduxContextData } from '../../routes';

/*Local*/
const SettingsRouters = () => {
	return (
		<>
			<AtrcRoutes>
				<AtrcRoute
					exact
					path='no-login/*'
					element={<NoLogin />}
				/>
				<AtrcRoute
					exact
					path='sidebar/*'
					element={<SidebarContent />}
				/>
				<AtrcRoute
					exact
					path='header/*'
					element={<HeaderContent />}
				/>
				<AtrcRoute
					exact
					path='elements/*'
					element={<Elements />}
				/>
				<AtrcRoute
					path='/'
					element={
						<AtrcNavigate
							to='no-login'
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
							to: 'no-login',
							children: __('Non-logged-in visitor', 'brand-master'),
						},
						{
							to: 'sidebar',
							children: __('Sidebar content', 'brand-master'),
						},
						{
							to: 'header',
							children: __('Header content', 'brand-master'),
						},
						{
							to: 'elements',
							children: __('Elements', 'brand-master'),
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
