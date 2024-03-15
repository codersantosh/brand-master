/* WordPress */
import { __ } from '@wordpress/i18n';

import { useContext } from '@wordpress/element';

/* Library */
import classNames from 'classnames';

/*Atrc*/
import { AtrcButton, AtrcWrap, AtrcHeaderTemplate1 } from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../routes';

/*Local*/
const AdminHeader = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	const primaryNav = [
		{
			to: '/',
			children: __('Getting started', 'brand-master'),
			end: true,
		},
		{
			to: '/login',
			children: __('Login', 'brand-master'),
		},
		{
			to: '/dashboard',
			children: __('Dashboard', 'brand-master'),
		},
		{
			to: '/settings',
			children: __('Settings', 'brand-master'),
		},
	];

	return (
		<AtrcHeaderTemplate1
			isSticky
			logo={{
				src: brandMasterLocalize.white_label.dashboard.logo,
			}}
			primaryNav={{
				navs: primaryNav,
			}}
			floatingSidebar={() => (
				<AtrcWrap className={classNames()}>
					<AtrcButton
						className={classNames()}
						onClick={() => lsSaveSettings(null)}>
						{__(
							'Show all hidden informations, notices and documentations ',
							'brand-master'
						)}
					</AtrcButton>
				</AtrcWrap>
			)}
		/>
	);
};

export default AdminHeader;
