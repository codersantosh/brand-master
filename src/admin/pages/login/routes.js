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
import { Slug, Advanced, Logo, Title, Utilities } from './pages';
import { AtrcReduxContextData } from '../../routes';
import { SaveSettings } from '../../components/atoms';

/*Local*/
const SettingsRouters = () => {
	return (
		<>
			<AtrcRoutes>
				<AtrcRoute
					exact
					path='url/*'
					element={<Slug />}
				/>
				<AtrcRoute
					exact
					path='logo/*'
					element={<Logo />}
				/>
				<AtrcRoute
					exact
					path='title/*'
					element={<Title />}
				/>
				<AtrcRoute
					exact
					path='utilities/*'
					element={<Utilities />}
				/>

				<AtrcRoute
					exact
					path='advanced/*'
					element={<Advanced />}
				/>
				<AtrcRoute
					path='/'
					element={
						<AtrcNavigate
							to='url'
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
							to: 'url',
							children: __('Security and URL', 'brand-master'),
						},
						{
							to: 'logo',
							children: __('Logo', 'brand-master'),
						},
						{
							to: 'title',
							children: __('Title', 'brand-master'),
						},
						{
							to: 'utilities',
							children: __('Utilities', 'brand-master'),
						},
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
